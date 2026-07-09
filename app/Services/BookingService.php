<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingCombo;
use App\Models\Promotion;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Combo;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    /**
     * Create a new booking with seats, optional combos, and optional voucher.
     *
     * @param int        $userId
     * @param int        $showtimeId
     * @param array      $showtimeSeatIds
     * @param array      $combosData   ['combo_id' => quantity]
     * @param array|null $voucherData  from session: promotion_id, discount_type, discount_value
     * @return Booking
     * @throws Exception
     */
    public function createBooking(int $userId, int $showtimeId, array $showtimeSeatIds, array $combosData, ?array $voucherData = null): Booking
    {
        if (empty($showtimeSeatIds)) {
            throw new Exception("Bạn phải chọn ít nhất một ghế.");
        }

        return DB::transaction(function () use ($userId, $showtimeId, $showtimeSeatIds, $combosData, $voucherData) {
            // 1. Lấy thông tin suất chiếu
            $showtime = Showtime::findOrFail($showtimeId);

            // 2. Khóa ghế để tránh race condition
            $showtimeSeats = ShowtimeSeat::with('seat.seatType')
                ->where('showtime_id', $showtimeId)
                ->whereIn('id', $showtimeSeatIds)
                ->lockForUpdate()
                ->get();

            if ($showtimeSeats->count() !== count($showtimeSeatIds)) {
                throw new Exception("Một số ghế bạn chọn không tồn tại.");
            }

            foreach ($showtimeSeats as $ss) {
                // Chấp nhận ghế do chính user này đang giữ (holding)
                if ($ss->status === 'holding' && $ss->user_id === $userId) {
                    continue;
                }
                if ($ss->status !== 'available') {
                    throw new Exception("Ghế " . $ss->seat->seat_row . $ss->seat->seat_number . " đã bị người khác đặt hoặc đang bị khóa.");
                }
            }

            // 3. Tính tiền ghế — ưu tiên dùng giá snapshot trong cột showtime_seats.price
            $totalSeatPrice = 0;
            $seatsPricing   = [];
            foreach ($showtimeSeats as $ss) {
                // Dùng giá đã được tính sẵn khi tạo suất chiếu (snapshot), fallback sang tính lại nếu chưa có
                $seatPrice = (isset($ss->price) && $ss->price > 0)
                    ? $ss->price
                    : $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
                $totalSeatPrice += $seatPrice;
                $seatsPricing[$ss->id] = $seatPrice;
            }

            // 4. Tính tiền combo
            $totalComboPrice = 0;
            $combosToInsert  = [];

            if (!empty($combosData)) {
                $combos = Combo::whereIn('id', array_keys($combosData))
                    ->where('status', 'active')
                    ->get();

                foreach ($combos as $combo) {
                    $qty = intval($combosData[$combo->id]);
                    if ($qty > 0) {
                        $subtotal         = $combo->price * $qty;
                        $totalComboPrice += $subtotal;
                        $combosToInsert[] = [
                            'combo_id' => $combo->id,
                            'quantity' => $qty,
                            'subtotal' => $subtotal,
                        ];
                    }
                }
            }

            // 5. Tạo booking code duy nhất
            do {
                $bookingCode = 'FG-' . strtoupper(Str::random(8));
            } while (Booking::where('booking_code', $bookingCode)->exists());

            // 6. Xử lý voucher — xác minh lại toàn diện promotion tại thời điểm confirm
            $discountAmount = 0;
            $promotionId    = null;

            if (!empty($voucherData) && isset($voucherData['promotion_id'])) {
                $promotion = Promotion::find($voucherData['promotion_id']);
                $now       = now();

                $isValid = $promotion
                    && $promotion->status === 'active'
                    && ($promotion->start_date === null || $now->gte($promotion->start_date))
                    && ($promotion->end_date === null || $now->lte($promotion->end_date));

                if ($isValid && $promotion->quantity !== null) {
                    $totalUsed = $promotion->bookings()->count();
                    if ($totalUsed >= $promotion->quantity) {
                        $isValid = false;
                    }
                }

                if ($isValid && $promotion->max_uses_per_user !== null) {
                    $usedByUser = $promotion->bookings()->where('user_id', $userId)->count();
                    if ($usedByUser >= $promotion->max_uses_per_user) {
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $subtotalForDiscount = $totalSeatPrice + $totalComboPrice;

                    if ($promotion->discount_type === 'percent') {
                        $discountAmount = (int) ($subtotalForDiscount * ($promotion->discount_value / 100));
                    } else {
                        $discountAmount = min($promotion->discount_value, $subtotalForDiscount);
                    }

                    $promotionId = $promotion->id;
                }
            }

            $totalAmount = max(0, $totalSeatPrice + $totalComboPrice - $discountAmount);

            // 7. Tạo đơn Booking
            $booking = Booking::create([
                'user_id'         => $userId,
                'showtime_id'     => $showtimeId,
                'booking_code'    => $bookingCode,
                'total_amount'    => $totalAmount,
                'discount_amount' => $discountAmount,
                'payment_status'  => 'pending',
                'booking_status'  => 'pending',
                'expired_at'      => now()->addMinutes(15),
            ]);

            // 8. Tạo Booking Details + Tickets + cập nhật trạng thái ghế
            foreach ($showtimeSeats as $ss) {
                $detail = BookingDetail::create([
                    'booking_id'       => $booking->id,
                    'showtime_seat_id' => $ss->id,
                    'price'            => $seatsPricing[$ss->id],
                ]);

                Ticket::create([
                    'booking_detail_id' => $detail->id,
                    'qr_code'           => 'TKT-' . Str::random(12) . '-' . time(),
                    'ticket_status'     => 'unused',
                ]);

                // Giữ ghế ở trạng thái 'holding' cho đến khi thanh toán xác nhận thành công
                $ss->update(['status' => 'holding', 'user_id' => $userId]);
            }

            // 9. Lưu combo đã đặt
            foreach ($combosToInsert as $cData) {
                BookingCombo::create([
                    'booking_id' => $booking->id,
                    'combo_id'   => $cData['combo_id'],
                    'quantity'   => $cData['quantity'],
                    'subtotal'   => $cData['subtotal'],
                ]);
            }

            // 10. Gắn voucher vào booking (bảng booking_promotions) — tự động tăng lượt dùng qua relationship
            if ($promotionId) {
                $booking->promotions()->attach($promotionId);
            }

            return $booking;
        });
    }
}
