<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketQrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTicketQrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * ID của vé cần sinh QR Code.
     */
    public int $ticketId;

    /**
     * Khởi tạo job với thông tin vé cần xử lý ở nền.
     */
    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    /**
     * Xử lý sinh QR Code cho vé cụ thể sau khi thanh toán được xác nhận.
     * Job này tách riêng khỏi luồng HTTP để hệ thống vẫn phản hồi nhanh cho khách hàng.
     */
    public function handle(TicketQrCodeService $qrService): void
    {
        /**
         * Tải vé cùng các dữ liệu liên quan để tạo payload chính xác.
         * Nếu vé đã có QR thì bỏ qua để tránh sinh trùng.
         */
        $ticket = Ticket::with(['bookingDetail' => function ($query) {
            $query->with(['showtimeSeat.seat', 'booking']);
        }])->find($this->ticketId);

        if (!$ticket || !empty($ticket->qr_code)) {
            return;
        }

        $bookingDetail = $ticket->bookingDetail;
        $showtimeSeat = $bookingDetail?->showtimeSeat;
        $seat = $showtimeSeat?->seat;
        $booking = $bookingDetail?->booking;
        $showtime = $showtimeSeat?->showtime;
        $movie = $showtime?->movie;

        if (!$booking || !$showtime || !$movie || !$seat) {
            return;
        }

        /**
         * Build payload vé dưới dạng dữ liệu nhỏ, có ý nghĩa và khó bị giả mạo.
         * Dữ liệu này sẽ được mã hóa trước khi chuyển thành ảnh QR.
         */
        $payload = [
            'ticket_id' => (int) $ticket->id,
            'order_id' => (string) $booking->booking_code,
            'movie_name' => (string) $movie->title,
            'show_time' => $showtime->show_date?->format('Y-m-d') . ' ' . $showtime->start_time,
            'seat' => strtoupper($seat->seat_row) . $seat->seat_number,
        ];

        /**
         * Gọi service sinh ảnh QR và lưu trực tiếp vào trường qr_code của vé.
         */
        $qrService->generateAndStoreQrForTicket($ticket, $payload);
    }
}
