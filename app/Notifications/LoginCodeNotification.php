<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code, private readonly int $minutes)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tizimga kirish kodi — ' . config('app.name'))
            ->greeting("Salom, {$notifiable->full_name}!")
            ->line('Tizimga kirish uchun quyidagi tasdiqlash kodidan foydalaning:')
            ->line(new \Illuminate\Support\HtmlString(
                "<div style=\"text-align:center;margin:24px 0\">".
                "<span style=\"display:inline-block;padding:12px 28px;font-size:28px;font-weight:700;letter-spacing:6px;background:#f3f4f6;border-radius:10px;color:#0f3460\">{$this->code}</span>".
                '</div>'
            ))
            ->line("Bu kod {$this->minutes} daqiqa davomida amal qiladi.")
            ->line("Agar tizimga kirishga urinmagan bo'lsangiz, bu xabarni e'tiborsiz qoldiring.");
    }
}
