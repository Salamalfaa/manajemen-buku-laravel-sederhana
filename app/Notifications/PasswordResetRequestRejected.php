<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $requesterName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permintaan reset password tidak disetujui')
            ->greeting('Halo '.$this->requesterName.',')
            ->line('Permintaan reset password akun Ruang Baca Anda tidak disetujui oleh administrator.')
            ->line('Jika Anda merasa ini keliru, silakan hubungi administrator aplikasi.');
    }
}
