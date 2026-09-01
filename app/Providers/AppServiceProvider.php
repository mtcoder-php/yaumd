<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin — har doim, hatto kelajakda yangi permission qo'shilib,
        // lekin seeder hali qayta ishlab chiqarilmagan holatda ham, barcha
        // "permission:" tekshiruvlaridan xavfsiz o'tadi. Bu Spatie'ning
        // rasman tavsiya qilgan "super-admin bypass" usuli — rol nomiga
        // (havola: RolePermissionSeeder'dagi 'super-admin') qattiq bog'langan.
        Gate::before(function ($user, string $ability) {
            return $user && $user->hasRole('super-admin') ? true : null;
        });

        // Laravel'ning standart "Parolni tiklash" xatini o'zbekcha va
        // loyihaning umumiy uslubiga moslashtiramiz (LoginCodeNotification
        // bilan bir xil ohangda).
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Parolni tiklash — ' . config('app.name'))
                ->greeting("Salom, {$notifiable->full_name}!")
                ->line("Hisobingiz uchun parolni tiklash so'ralgan. Agar bu so'rovni siz yubormagan bo'lsangiz, hech narsa qilishingiz shart emas — parolingiz o'zgarmaydi.")
                ->action('Parolni tiklash', $url)
                ->line('Xavfsizlik yuzasidan ushbu havola 60 daqiqa davomida amal qiladi.');
        });
    }
}
