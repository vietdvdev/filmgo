<?php

namespace App\Services;

use Illuminate\Support\Str;

class TicketQrCodeService
{
    /**
     * Khóa bí mật dùng để mã hóa/giải mã payload vé nếu cần.
     */
    private string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? env('TICKET_QR_SECRET', Str::random(32));
    }

    /**
     * Lấy URL hiển thị mã QR trực tiếp từ chuỗi mã vé.
     * Hoạt động tức thì 0ms, hiển thị sắc nét và không phụ thuộc Queue Job.
     */
    public function getQrImageUrl(?string $qrCodeText): string
    {
        if (empty($qrCodeText)) {
            $qrCodeText = 'FILMGO-' . Str::upper(Str::random(10));
        }

        if (str_starts_with($qrCodeText, 'data:image/') || str_starts_with($qrCodeText, 'http://') || str_starts_with($qrCodeText, 'https://')) {
            return $qrCodeText;
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrCodeText);
    }

    /**
     * Đồng bộ mã định danh QR duy nhất ngắn gọn cho tất cả các vé của đơn hàng (Booking).
     * Đảm bảo chuỗi ngắn vừa vặn với cột VARCHAR(255) trong MySQL, không bao giờ bị lỗi Data too long.
     */
    public function generateAndStoreForBooking(\App\Models\Booking $booking): void
    {
        $booking->loadMissing([
            'bookingDetails.ticket',
        ]);

        foreach ($booking->bookingDetails as $detail) {
            $ticket = $detail->ticket;
            if (!$ticket) {
                continue;
            }

            // Nếu vé chưa có mã định danh thì tạo mã độc nhất ngắn gọn
            if (empty($ticket->qr_code)) {
                $prefix = ($booking->booking_type === 'combo_only' || !$booking->showtime_id) ? 'CB' : 'TKT';
                $ticket->update([
                    'qr_code' => $prefix . '-' . Str::upper(Str::random(10)) . '-' . $ticket->id,
                ]);
            }
        }
    }

    /**
     * Tương thích ngược với các gọi hàm cũ.
     */
    public function generateAndStoreQrForTicket($ticket, array $payload): string
    {
        $uniqueCode = 'TKT-' . Str::upper(Str::random(10)) . '-' . ($ticket->id ?? rand(100, 999));
        $ticket->update([
            'qr_code' => $uniqueCode,
        ]);
        return $uniqueCode;
    }
}
