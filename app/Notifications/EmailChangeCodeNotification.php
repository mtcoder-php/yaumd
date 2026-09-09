<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Profildan email almashtirilganda YANGI manzilga yuboriladi (u manzilni
 * haqiqatan ham foydalanuvchi boshqarishini tasdiqlash uchun) —
 * App\Notifications\LoginCodeNotification'dan farqli o'laroq, bu
 * $user->notify() orqali emas, balki Notification::route('mail', ...)
 * orqali hali bazada YO'Q manzilga yuboriladi (ProfileController'ga
 * qarang), shuning uchun foydalanuvchi ismi konstruktorga alohida
 * uzatiladi.
 */
class EmailChangeCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $fullName,
        private readonly int $minutes
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Yangi emailni tasdiqlash — ' . config('app.name'))
            ->greeting("Salom, {$this->fullName}!")
            ->line("Hisobingizdagi email manzilni ushbu manzilga o'zgartirish so'ralgan. Tasdiqlash uchun quyidagi koddan foydalaning:")
            ->line(new HtmlString(
                '<div style="text-align:center;margin:24px 0">' .
                '<span style="display:inline-block;padding:12px 28px;font-size:28px;font-weight:700;letter-spacing:6px;background:#f3f4f6;border-radius:10px;color:#0f3460">' . $this->code . '</span>' .
                '</div>'
            ))
            ->line("Bu kod {$this->minutes} daqiqa davomida amal qiladi.")
            ->line("Agar bu so'rovni siz yubormagan bo'lsangiz, bu xabarni e'tiborsiz qoldiring va hisobingiz parolini tekshiring.");
    }
}
