<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCombo;
use App\Models\BookingDetail;
use App\Models\Combo;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CounterBookingService
{
    /**
     * Tạo đơn bán vé tại quầy (POS).
     *
     * Khác biệt so với online:
     *  - Giao dịch hoàn tất ngay (không chờ IPN callback).
     *  - Booking status = 'confirmed', payment_status = 'paid'.
     *  - Hỗ trợ khách vãng lai (user_id = null).
     *  - Lưu staff_id + channel = 'counter'.
     *
     * @param  int         $staffId         ID nhân viên thực hiện
     * @param  int         $showtimeId      ID suất chiếu
     * @param  int[]       $showtimeSeatIds Danh sách ID showtime_seats đã chọn
     * @param  array       $combosData      ['combo_id' => quantity, ...]
     * @param  string      $paymentMethod   'cash' | 'transfer'
     * @param  string|null $customerPhone   SĐT khách hàng (tuỳ chọn, để tích điểm)
     * @param  string|null $voucherCode     Mã giảm giá (tuỳ chọn)
     * @return Booking
     *
     * @throws Exception
     */
    public function createCounterBooking(
        int $staffId,
        int $showtimeId,
        array $showtimeSeatIds,
        array $combosData = [],
        string $paymentMethod = 'cash',
        ?string $customerPhone = null,
        ?string $voucherCode = null
    ): Booking {
        if (empty($showtimeSeatIds)) {
            throw new Exception('Vui lòng chọn ít nhất một ghế.');
        }

        return DB::transaction(function () use (
            $staffId, $showtimeId, $showtimeSeatIds,
            $combosData, $paymentMethod, $customerPhone, $voucherCode
        ) {
            // ── 1. Kiểm tra suất chiếu còn hợp lệ ──────────────────────────
            $showtime = Showtime::with(['room'])->findOrFail($showtimeId);

            if (in_array($showtime->status, ['cancelled', 'finished'])) {
                throw new Exception('Suất chiếu này đã bị hủy hoặc kết thúc.');
            }

            $showtimeEnd = Carbon::parse($showtime->show_date->format('Y-m-d') . ' ' . $showtime->end_time);
            if (now()->gt($showtimeEnd)) {
                throw new Exception('Suất chiếu đã kết thúc lúc ' . $showtimeEnd->format('H:i d/m/Y') . '. Không thể bán vé.');
            }

            // ── 2. Khoá ghế bằng SELECT FOR UPDATE (tránh race condition) ──
            $showtimeSeats = ShowtimeSeat::with('seat.seatType')
                ->where('showtime_id', $showtimeId)
                ->whereIn('id', $showtimeSeatIds)
                ->lockForUpdate()
                ->get();

            if ($showtimeSeats->count() !== count($showtimeSeatIds)) {
                throw new Exception('Một số ghế không tồn tại trong suất chiếu này.');
            }

            foreach ($showtimeSeats as $ss) {
                // Ghế 'booked' (đã bán hoàn tất) thì không thể bán lại
                if ($ss->status === 'booked') {
                    throw new Exception(
                        "Ghế {$ss->seat->seat_row}{$ss->seat->seat_number} đã được bán. Vui lòng chọn ghế khác."
                    );
                }
                // Ghế đang được khách online giữ (holding) bởi người khác
                if ($ss->status === 'holding' && $ss->user_id !== $staffId) {
                    throw new Exception(
                        "Ghế {$ss->seat->seat_row}{$ss->seat->seat_number} đang tạm giữ bởi khách online. Vui lòng chọn ghế khác."
                    );
                }
            }

            // ── 3. Tìm khách hàng theo SĐT (nếu có) ─────────────────────
            $customerId = null;
            if ($customerPhone) {
                $customer   = User::where('phone', $customerPhone)->first();
                $customerId = $customer?->id;
            }

            // ── 4. Tính giá ghế (ưu tiên snapshot, fallback tính lại) ────
            $totalSeatPrice = 0;
            $seatsPricing   = [];

            foreach ($showtimeSeats as $ss) {
                $seatPrice = (isset($ss->price) && $ss->price > 0)
                    ? $ss->price
                    : $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
                $totalSeatPrice          += $seatPrice;
                $seatsPricing[$ss->id]    = $seatPrice;
            }

            // ── 5. Tính giá combo ─────────────────────────────────────────
            $totalComboPrice = 0;
            $combosToInsert  = [];

            if (!empty($combosData)) {
                $combos = Combo::whereIn('id', array_keys($combosData))
                    ->where('status', 'active')
                    ->get();

                foreach ($combos as $combo) {
                    $qty = intval($combosData[$combo->id] ?? 0);
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

            // ── 6. Xử lý voucher (xác minh đầy đủ tại thời điểm confirm) ─
            $discountAmount = 0;
            $promotionId    = null;

            if ($voucherCode) {
                $promotion = Promotion::where('code', strtoupper(trim($voucherCode)))->first();
                $now       = now();

                $isValid = $promotion
                    && $promotion->status === 'active'
                    && ($promotion->start_date === null || $now->gte($promotion->start_date))
                    && ($promotion->end_date   === null || $now->lte($promotion->end_date));

                /**
                 * Dùng cột used_count (đã có sẵn) thay vì gọi bookings()->count().
                 * Tránh thêm 1 COUNT query mỗi lần check voucher — đặc biệt quan trọng ở POS.
                 */
                if ($isValid && $promotion->usage_limit !== null) {
                    if ($promotion->used_count >= $promotion->usage_limit) {
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $subtotalForDiscount = $totalSeatPrice + $totalComboPrice;

                    $discountAmount = $promotion->discount_type === 'percent'
                        ? (int) ($subtotalForDiscount * ($promotion->discount_value / 100))
                        : min($promotion->discount_value, $subtotalForDiscount);

                    $promotionId = $promotion->id;
                }
            }

            $totalAmount = max(0, $totalSeatPrice + $totalComboPrice - $discountAmount);

            // ── 7. Sinh booking code duy nhất ────────────────────────────
            do {
                $bookingCode = 'CTR-' . strtoupper(Str::random(8));
            } while (Booking::where('booking_code', $bookingCode)->exists());

            // ── 8. Tạo đơn Booking — hoàn tất ngay, không cần callback ──
            $subtotal = $totalSeatPrice + $totalComboPrice; // Trước khi giảm giá

            $booking = Booking::create([
                'user_id'         => $customerId,
                'staff_id'        => $staffId,
                'showtime_id'     => $showtimeId,
                'booking_code'    => $bookingCode,
                'subtotal'        => $subtotal,         // Tổng trước giảm giá
                'total_amount'    => $subtotal,         // Giá gốc (không áp giảm)
                'discount_amount' => $discountAmount,
                'final_total'     => $totalAmount,      // Số tiền thực thu sau giảm giá
                'payment_status'  => 'paid',            // Hoàn tất ngay tại quầy
                'booking_status'  => 'confirmed',       // Xác nhận ngay, không chờ IPN
                'channel'         => 'counter',         // Kênh tại quầy
                'expired_at'      => now()->addMinutes(30),
            ]);

            /**
             * 9. Tạo Booking Details + Tickets + khóa ghế.
             *
             * BATCH INSERT: Thay vì tạo từng record trong vòng lặp (N*3 queries),
             * thu thập vào mảng rồi insert hàng loạt — giảm xuống còn 3 queries cố định.
             */
            $bookingDetailsData = [];

            foreach ($showtimeSeats as $ss) {
                $bookingDetailsData[] = [
                    'booking_id'       => $booking->id,
                    'showtime_seat_id' => $ss->id,
                    'price'            => $seatsPricing[$ss->id],
                ];
            }

            // Batch insert tất cả booking_details — 1 query
            BookingDetail::insert($bookingDetailsData);

            // Lấy lại các detail vừa insert để tạo tickets
            $insertedDetails = BookingDetail::where('booking_id', $booking->id)
                ->get()
                ->keyBy('showtime_seat_id');

            $ticketsData = [];
            foreach ($showtimeSeats as $ss) {
                $detail = $insertedDetails->get($ss->id);
                if ($detail) {
                    $ticketsData[] = [
                        'booking_detail_id' => $detail->id,
                        // Prefix CTR phân biệt vé quầy, QR duy nhất bằng random + seat ID
                        'qr_code'           => 'CTR-' . Str::upper(Str::random(12)) . '-' . $ss->id,
                        'ticket_status'     => 'unused',
                    ];
                }
            }

            // Batch insert tất cả tickets — 1 query
            if (!empty($ticketsData)) {
                Ticket::insert($ticketsData);
            }

            /**
             * Batch UPDATE trạng thái ghế về 'booked' ngay lập tức (POS hoàn tất ngay).
             * Dùng whereIn() — 1 UPDATE thay vì N UPDATE trong vòng lặp.
             */
            $seatIdsToUpdate = array_column($bookingDetailsData, 'showtime_seat_id');
            ShowtimeSeat::whereIn('id', $seatIdsToUpdate)
                ->update([
                    'status'     => 'booked',
                    'user_id'    => $customerId ?? $staffId,
                    'locked_at'  => now(),
                    'expires_at' => null,
                ]);

            // ── 10. Lưu combo ─────────────────────────────────
            if (!empty($combosToInsert)) {
                // Batch insert combos — 1 query thay vì N query
                $combosInsertData = array_map(fn ($cData) => [
                    'booking_id' => $booking->id,
                    'combo_id'   => $cData['combo_id'],
                    'quantity'   => $cData['quantity'],
                    'subtotal'   => $cData['subtotal'],
                ], $combosToInsert);

                BookingCombo::insert($combosInsertData);
            }

            // ── 11. Gắn voucher ──────────────────────────────────────────
            if ($promotionId) {
                $booking->promotions()->attach($promotionId);
            }

            // ── 12. Tạo bản ghi Payment (Thanh toán hoàn tất) ───────────
            Payment::create([
                'booking_id'      => $booking->id,
                'transaction_code' => 'CTR-' . strtoupper(Str::random(10)),
                'amount'          => $totalAmount,
                'payment_method'  => $paymentMethod, // 'cash' | 'transfer'
                'payment_status'  => 'success',
                'paid_at'         => now(),
            ]);

            return $booking->load([
                'bookingDetails.showtimeSeat.seat.seatType',
                'bookingDetails.ticket',
                'combos',
                'showtime.movie',
                'showtime.room.cinema',
            ]);
        });
    }

    /**
     * Lấy sơ đồ ghế real-time cho màn hình POS.
     * Trả về dữ liệu gộp: thông tin ghế + trạng thái tức thời.
     *
     * @param  int   $showtimeId
     * @return array
     */
    public function getSeatMapData(int $showtimeId): array
    {
        $showtime = Showtime::with(['room', 'movie'])->findOrFail($showtimeId);

        $showtimeSeats = ShowtimeSeat::with('seat.seatType')
            ->where('showtime_id', $showtimeId)
            ->get()
            ->sortBy(fn($ss) => $ss->seat->seat_row . sprintf('%03d', $ss->seat->seat_number));

        $rows = $showtimeSeats->pluck('seat.seat_row')->unique()->sort()->values();

        $seats = $showtimeSeats->map(fn($ss) => [
            'showtime_seat_id' => $ss->id,
            'seat_id'          => $ss->seat_id,
            'row'              => $ss->seat->seat_row,
            'number'           => $ss->seat->seat_number,
            'label'            => $ss->seat->seat_row . $ss->seat->seat_number,
            'type'             => $ss->seat->seatType->name ?? 'Thường',
            'surcharge'        => $ss->seat->seatType->surcharge_price ?? 0,
            'seat_status'      => $ss->seat->status, // Trạng thái vật lý của ghế
            'status'           => $ss->status,        // Trạng thái trong suất chiếu
            'price'            => (isset($ss->price) && $ss->price > 0)
                                    ? $ss->price
                                    : $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0),
        ]);

        return [
            'showtime'   => [
                'id'         => $showtime->id,
                'movie'      => $showtime->movie->title,
                'room'       => $showtime->room->room_name,
                'room_type'  => $showtime->room->room_type,
                'show_date'  => $showtime->show_date,
                'start_time' => $showtime->start_time,
                'end_time'   => $showtime->end_time,
                'base_price' => $showtime->base_price,
                'status'     => $showtime->status,
            ],
            'rows'  => $rows->values(),
            'seats' => $seats->values(),
        ];
    }
}
