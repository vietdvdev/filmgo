<?php

namespace App\Services;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingEmailService
{
    /**
     * Gửi email xác nhận đơn hàng (Vé phim và/hoặc Combo) cho khách hàng.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendConfirmationEmail(Booking $booking): bool
    {
        try {
            // Nạp đầy đủ quan hệ cần thiết nếu chưa load
            $booking->loadMissing([
                'user',
                'showtime.movie',
                'showtime.room.cinema',
                'bookingDetails.showtimeSeat.seat.seatType',
                'combos',
            ]);

            $userEmail = $booking->user?->email;

            if (empty($userEmail)) {
                Log::warning("BookingEmailService: Không tìm thấy email của người dùng cho đơn đặt #{$booking->booking_code}");
                return false;
            }

            // Gửi mail xác nhận
            Mail::to($userEmail)->send(new BookingConfirmationMail($booking));

            Log::info("BookingEmailService: Đã gửi email xác nhận thành công tới [{$userEmail}] cho đơn hàng #{$booking->booking_code}");
            return true;

        } catch (\Throwable $e) {
            // Bắt ngoại lệ để không làm gián đoạn luồng hoàn tất đơn hàng
            Log::error("BookingEmailService: Lỗi khi gửi email xác nhận đơn hàng #{$booking->booking_code}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }
}
