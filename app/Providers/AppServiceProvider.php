<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Observers\PaymentObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // To'lov "to'landi" holatiga o'tganda talabaga darhol Telegram
        // orqali tasdiq xabari yuborish uchun (PaymentObserver'ga qarang) —
        // kassir qo'lda kiritganda ham, Click/Payme callback tasdiqlaganda
        // ham ishlaydi, chunki ikkalasi ham oxir-oqibat shu Payment
        // modelini yaratadi/yangilaydi.
        Payment::observe(PaymentObserver::class);

        // Turniket (Face ID) "employeeNo" moslashtiruvi uchun (PersonMatch,
        // TurnstileEvent.matched_type) — bazada to'liq class nomi
        // (masalan "App\\Models\\Student") o'rniga qisqa "student"/"staff"
        // satri saqlanadi. Sabablari: (1) turnstile_events.matched_type
        // ustuni allaqachon shu qisqa konvensiya bilan yaratilgan edi,
        // (2) kelajakda model joyini/nomini o'zgartirsak ham, bazadagi
        // eski yozuvlar buzilib qolmaydi.
        Relation::morphMap([
            'student' => Student::class,
            'staff'   => User::class,
        ]);
    }
}
