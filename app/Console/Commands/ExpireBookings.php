<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ShowtimeSeat;
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
     */
    public function handle(): int
    {
        $now = now();

        // Lấy các booking đang ở trạng thái chờ thanh toán nhưng đã quá hạn theo cột expired_at.
        $expiredBookings = Booking::query()
            ->where('payment_status', 'pending')
            ->where('booking_status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $now)
            ->with(['bookingDetails.showtimeSeat', 'bookingDetails.ticket'])
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('Không có booking nào cần xử lý hết hạn lúc ' . $now->toDateTimeString() . '.');

            return self::SUCCESS;
        }

        $expiredCount = 0;

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking): void {
                // Tái tải booking để tránh trường hợp dữ liệu đã bị thay đổi bởi luồng khác.
                $booking->refresh();

                // Nếu booking đã được xử lý trước đó thì bỏ qua.
                if ($booking->payment_status !== 'pending' || $booking->booking_status !== 'pending') {
                    return;
                }

                // Cập nhật booking sang trạng thái hủy do hết hạn.
                $booking->update([
                    'payment_status' => 'failed',
                    'booking_status' => 'cancelled',
                ]);

                // Cập nhật vé của booking sang trạng thái hủy.
                foreach ($booking->bookingDetails as $detail) {
                    if ($detail->ticket) {
                        $detail->ticket->update(['ticket_status' => 'cancelled']);
                    }
                }

                // Nhả ghế về trạng thái available nếu ghế vẫn đang thuộc booking này.
                foreach ($booking->bookingDetails as $detail) {
                    $seat = $detail->showtimeSeat;

                    if ($seat) {
                        $seat->update([
                            'status' => 'available',
                            'user_id' => null,
                        ]);
                    }
                }
            });

            $expiredCount++;
        }

        Log::info('Bookings expired processed', [
            'count' => $expiredCount,
            'processed_at' => $now->toDateTimeString(),
        ]);

        $this->info("Đã xử lý {$expiredCount} booking hết hạn thanh toán.");

        return self::SUCCESS;
    }
}
