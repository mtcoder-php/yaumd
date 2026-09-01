<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class StudentAccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $password)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tizimga kirish ma'lumotlaringiz — " . config('app.name'))
            ->greeting("Salom, {$notifiable->full_name}!")
            ->line(config('app.name') . " tizimida siz uchun shaxsiy kabinet ochildi. Quyidagi ma'lumotlar orqali tizimga kirishingiz mumkin:")
            ->line(new HtmlString(
                '<div style="margin:20px 0;padding:16px 20px;background:#f3f4f6;border-radius:10px">' .
                '<div style="margin-bottom:8px">' . e('Login (email):') . ' <strong>' . e($notifiable->email) . '</strong></div>' .
                '<div>' . e('Vaqtinchalik parol:') . ' <span style="font-weight:700;letter-spacing:1px;color:#0f3460">' . e($this->password) . '</span></div>' .
                '</div>'
            ))
            ->line('Xavfsizlik yuzasidan tizimga birinchi marta kirgach, parolingizni o\'zgartirishingizni tavsiya qilamiz — buning uchun kirish sahifasidagi "Parolni unutdingizmi?" havolasidan foydalanishingiz mumkin.')
            ->line("Agar bu hisob siz uchun yaratilmagan bo'lsa, iltimos administratsiyaga murojaat qiling.");
    }
}
