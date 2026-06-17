<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email
        ]);

        return (new MailMessage)
            ->subject('FilmGo - Yêu Cầu Khôi Phục Mật Khẩu')
            ->view('emails.reset_password', [
                'url' => $url,
                'name' => $notifiable->full_name
            ]);
    }
}
