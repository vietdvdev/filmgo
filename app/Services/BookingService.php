<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingCombo;
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
     * Create a new booking with seats and optional combos.
     *
     * @param int $userId
     * @param int $showtimeId
     * @param array $showtimeSeatIds
     * @param array $combosData Array of ['id' => quantity]
     * @return Booking
     * @throws Exception
     */
    public function createBooking(int $userId, int $showtimeId, array $showtimeSeatIds, array $combosData): Booking
    {
        if (empty($showtimeSeatIds)) {
            throw new Exception("Bạn phải chọn ít nhất một ghế.");
        }

        return DB::transaction(function () use ($userId, $showtimeId, $showtimeSeatIds, $combosData) {
            // 1. Lấy thông tin suất chiếu
            $showtime = Showtime::findOrFail($showtimeId);

            // 2. Lấy thông tin ghế của suất chiếu và khóa để tránh tranh chấp (pessimistic lock)
            $showtimeSeats = ShowtimeSeat::with('seat.seatType')
                ->where('showtime_id', $showtimeId)
                ->whereIn('id', $showtimeSeatIds)
                ->lockForUpdate()
                ->get();

            if ($showtimeSeats->count() !== count($showtimeSeatIds)) {
                throw new Exception("Một số ghế bạn chọn không tồn tại.");
            }

            // Kiểm tra trạng thái của từng ghế
            foreach ($showtimeSeats as $ss) {
                if ($ss->status !== 'available') {
                    throw new Exception("Ghế " . ($ss->seat->seat_row . $ss->seat->seat_number) . " đã bị người khác đặt hoặc đang bị khóa.");
                }
            }

            // 3. Tính toán tổng tiền ghế
            $totalSeatPrice = 0;
            $seatsPricing = [];
            foreach ($showtimeSeats as $ss) {
                $seatPrice = $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
                $totalSeatPrice += $seatPrice;
                $seatsPricing[$ss->id] = $seatPrice;
            }

            // 4. Tính toán tổng tiền Combo bắp nước
            $totalComboPrice = 0;
            $combosToInsert = [];
            
            if (!empty($combosData)) {
                $comboIds = array_keys($combosData);
                $combos = Combo::whereIn('id', $comboIds)
                    ->where('status', 'active')
                    ->get();

                foreach ($combos as $combo) {
                    $qty = intval($combosData[$combo->id]);
                    if ($qty > 0) {
                        $subtotal = $combo->price * $qty;
                        $totalComboPrice += $subtotal;
                        $combosToInsert[] = [
                            'combo_id' => $combo->id,
                            'quantity' => $qty,
                            'subtotal' => $subtotal
                        ];
                    }
                }
            }

            // 5. Tạo mã đặt vé duy nhất
            do {
                $bookingCode = 'FG-' . strtoupper(Str::random(8));
            } while (Booking::where('booking_code', $bookingCode)->exists());

            // 6. Tạo đơn Booking
            $booking = Booking::create([
                'user_id' => $userId,
                'showtime_id' => $showtimeId,
                'booking_code' => $bookingCode,
                'total_amount' => $totalSeatPrice + $totalComboPrice,
                'discount_amount' => 0,
                'payment_status' => 'pending',
                'booking_status' => 'pending',
                'expired_at' => now()->addMinutes(15),
            ]);

            // 7. Tạo Booking Details, Tickets, và cập nhật trạng thái Ghế
            foreach ($showtimeSeats as $ss) {
                // Tạo chi tiết đặt vé
                $detail = BookingDetail::create([
                    'booking_id' => $booking->id,
                    'showtime_seat_id' => $ss->id,
                    'price' => $seatsPricing[$ss->id],
                ]);

                // Tạo vé điện tử (Ticket) tương ứng
                Ticket::create([
                    'booking_detail_id' => $detail->id,
                    'qr_code' => 'TKT-' . Str::random(12) . '-' . time(),
                    'ticket_status' => 'unused',
                ]);

                // Cập nhật trạng thái ghế sang booked
                $ss->update([
                    'status' => 'booked',
                    'user_id' => $userId
                ]);
            }

            // 8. Lưu Combo đã đặt
            foreach ($combosToInsert as $cData) {
                BookingCombo::create([
                    'booking_id' => $booking->id,
                    'combo_id' => $cData['combo_id'],
                    'quantity' => $cData['quantity'],
                    'subtotal' => $cData['subtotal'],
                ]);
            }

            return $booking;
        });
    }
}
