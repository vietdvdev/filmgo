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
     * Hỗ trợ cả vé xem phim (có seat) và ticket đại diện cho đơn combo (không có seat).
     * Job này tách riêng khỏi luồng HTTP để hệ thống vẫn phản hồi nhanh cho khách hàng.
     */
    public function handle(TicketQrCodeService $qrService): void
    {
        $ticket = Ticket::find($this->ticketId);
        if ($ticket && empty($ticket->qr_code)) {
            $ticket->update([
                'qr_code' => 'TKT-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)) . '-' . $ticket->id,
            ]);
        }
    }
}
