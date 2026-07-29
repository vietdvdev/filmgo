<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireBookings extends Command
{
    /**
     * Tên lệnh console để chạy định kỳ.
     *
     * @var string
     */
    protected $signature = 'bookings:expire';

    /**
     * Mô tả ngắn cho lệnh.
     *
     * @var string
     */
    protected $description = 'Tự động hủy các booking đã quá hạn thanh toán và nhả ghế về trạng thái có thể đặt lại.';

    /**
     * Thực thi luồng hết hạn booking.
     *
     * Tối ưu:
     * - Thay vòng lặp update từng record bằng batch UPDATE dùng whereIn().
     * - Gộp tất cả cập nhật ticket, ghế vào 2 query thay vì N query.
     * - Chỉ chạy 1 transaction bao toàn bộ thay vì N transaction riêng lẻ.
     */
    public function handle(): int
    {
        $now = now();

        /**
         * Lấy danh sách booking hết hạn — CHỈ cần ID để batch update.
         * Không cần eager load relations vì chúng ta sẽ query qua BookingDetail.
         */
        $expiredBookingIds = Booking::pending()  // payment_status = 'pending'
            ->where('booking_status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $now)
            ->pluck('id')
            ->toArray();

        if (empty($expiredBookingIds)) {
            $this->info('Không có booking nào cần xử lý hết hạn lúc ' . $now->toDateTimeString() . '.');
            return self::SUCCESS;
        }

        $count = count($expiredBookingIds);

        /**
         * BATCH UPDATE trong 1 transaction duy nhất.
         * Thay vì N transaction riêng (mỗi booking 1 transaction), gom tất cả vào 1 transaction.
         * Giúp giảm overhead kết nối DB và đảm bảo atomic cho toàn bộ lô hết hạn.
         */
        DB::transaction(function () use ($expiredBookingIds) {
            // ── 1. Batch UPDATE booking sang cancelled ──────────────────────
            Booking::whereIn('id', $expiredBookingIds)
                ->update([
                    'payment_status' => 'failed',
                    'booking_status' => 'cancelled',
                ]);

            // ── 2. Lấy tất cả showtime_seat_id của các booking hết hạn ─────
            $showtimeSeatIds = BookingDetail::whereIn('booking_id', $expiredBookingIds)
                ->pluck('showtime_seat_id')
                ->toArray();

            // ── 3. Lấy tất cả ticket_id cần hủy ────────────────────────────
            $bookingDetailIds = BookingDetail::whereIn('booking_id', $expiredBookingIds)
                ->pluck('id')
                ->toArray();

            // ── 4. Batch UPDATE tickets sang cancelled ──────────────────────
            if (!empty($bookingDetailIds)) {
                // Cập nhật tất cả vé của các booking hết hạn bằng 1 query
                Ticket::whereIn('booking_detail_id', $bookingDetailIds)
                    ->update(['ticket_status' => 'cancelled']);
            }

            // ── 5. Batch UPDATE ghế về available ────────────────────────────
            if (!empty($showtimeSeatIds)) {
                /**
                 * Nhả ghế về available bằng 1 UPDATE whereIn() thay vì N UPDATE riêng lẻ.
                 * Không dùng scope expired() vì ghế có thể đã được giải phóng bởi luồng khác.
                 * Điều kiện whereIn đảm bảo chỉ update đúng ghế của các booking vừa hủy.
                 */
                ShowtimeSeat::whereIn('id', $showtimeSeatIds)
                    ->whereIn('status', ['holding', 'locked', 'booked'])
                    ->update([
                        'status'     => 'available',
                        'user_id'    => null,
                        'locked_at'  => null,
                        'expires_at' => null,
                    ]);
            }
        });

        Log::info('Bookings expired processed', [
            'count'        => $count,
            'booking_ids'  => $expiredBookingIds,
            'processed_at' => $now->toDateTimeString(),
        ]);

        $this->info("Đã xử lý {$count} booking hết hạn thanh toán.");

        return self::SUCCESS;
    }
}

