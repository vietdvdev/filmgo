<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo đối tượng Mailable với đơn Booking.
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Cấu hình tiêu đề Email.
     */
    public function envelope(): Envelope
    {
        $isComboOnly = ($this->booking->booking_type === 'combo_only') || !$this->booking->showtime_id;
        $subject = $isComboOnly
            ? "FilmGo - Xác nhận đơn mua Bắp Nước & Combo [#{$this->booking->booking_code}]"
            : "FilmGo - Xác nhận đơn đặt vé xem phim [#{$this->booking->booking_code}]";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Cấu hình View template hiển thị.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_confirmation',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    /**
     * Các tệp đính kèm (nếu có).
     */
    public function attachments(): array
    {
        return [];
    }
}
