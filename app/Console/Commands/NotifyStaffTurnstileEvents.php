<?php

namespace App\Console\Commands;

use App\Models\TurnstileEvent;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Console\Command;

/**
 * Xodim turniketdan o'tgan (kirdi/chiqdi) HAR bir voqea uchun DARHOL ikki
 * xil xabar yuboradi:
 *   1) Agar xodimning o'zi Telegram botga ulangan bo'lsa — unga shaxsiy
 *      qisqa xabar ("soat X da keldingiz/chiqdingiz").
 *   2) Agar HR uchun guruh/chat sozlangan bo'lsa ('TELEGRAM_HR_CHAT_ID')
 *      — o'sha guruhga xodimning ismi bilan bir xil voqea haqida xabar
 *      (xodim o'z botiga ulanmagan bo'lsa ham, HR baribir ko'rishi kerak).
 *
 * Bu — NotifyStudentTurnstileEvents'ning xodimlar uchun soddalashtirilgan
 * ko'rinishi (to'lov/qarz qismi yo'q, chunki xodimlarda kontrakt degan
 * tushuncha yo'q) — MUHIM: NotifyStaffAttendance (kech qolish/erta ketish,
 * kunlik bir marta kechqurun) bilan ALMASHTIRMAYDI, uni TO'LDIRADI — bu
 * buyruq har bir voqeada DARHOL ishlaydi, o'sha esa kun oxirida FAQAT
 * birinchi kirish/oxirgi chiqishni solishtiradi.
 *
 * MUHIM: 'turnstile:sync-events' va 'turnstile:match-people'dan KEYIN
 * ishga tushishi kerak (routes/console.php'dagi tartibga qarang) — faqat
 * ALLAQACHON 'staff'ga moslashtirilgan va hali xabar yuborilmagan
 * ('notified_at' bo'sh) voqealarni ko'radi.
 */
class NotifyStaffTurnstileEvents extends Command
{
    protected $signature = 'turnstile:notify-staff {--limit=200}';

    protected $description = "Telegram botga ulangan xodimlarga va HR guruhiga har bir turniket (kirish/chiqish) voqeasi haqida darhol xabar yuboradi";

    public function handle(TelegramService $telegram): int
    {
        $hrChatId = (string) config('services.telegram.hr_chat_id');

        $events = TurnstileEvent::query()
            ->whereNull('notified_at')
            ->where('matched_type', 'staff')
            ->whereNotNull('matched_id')
            ->where('major', TurnstileEvent::MAJOR_ACCESS_CONTROL)
            ->where('minor', TurnstileEvent::MINOR_FACE_RECOGNIZED)
            ->with('device')
            ->orderBy('event_time')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($events->isEmpty()) {
            return self::SUCCESS;
        }

        $sentToStaff = 0;
        $sentToHr = 0;

        foreach ($events as $event) {
            $user = User::find($event->matched_id);

            if (! $user) {
                $event->update(['notified_at' => now()]);

                continue;
            }

            $time = $event->event_time->format('H:i');
            $direction = match ($event->device?->direction) {
                'kirish' => 'kirdingiz',
                'chiqish' => 'chiqdingiz',
                default => "o'tdingiz",
            };
            $icon = $event->device?->direction === 'chiqish' ? '🚶' : '🚪';

            if ($user->telegram_chat_id) {
                // MUHIM: sendMessage() natijasi (true/false) albatta
                // tekshiriladi — aks holda Telegram API xato qaytarsa ham
                // (masalan noto'g'ri chat_id, bot bloklangan) buyruq
                // "yuborildi" deb hisoblab, xatoni butunlay yashirib
                // qo'yardi (storage/logs/laravel.log'da haqiqiy sabab
                // yoziladi — TelegramService'ga qarang).
                if ($telegram->sendMessage(
                    $user->telegram_chat_id,
                    "{$icon} Siz bugun soat <b>{$time}</b> da {$direction}."
                )) {
                    $sentToStaff++;
                }
            }

            if ($hrChatId !== '') {
                $directionLabel = match ($event->device?->direction) {
                    'kirish' => 'keldi',
                    'chiqish' => 'ketdi',
                    default => "o'tdi",
                };
                $deviceName = $event->device?->name ?? "noma'lum terminal";

                if ($telegram->sendMessage(
                    $hrChatId,
                    "{$icon} <b>{$user->full_name}</b> bugun soat <b>{$time}</b> da {$directionLabel} ({$deviceName})."
                )) {
                    $sentToHr++;
                }
            }

            // Xodimning o'zi ham, HR ham xabar olmagan bo'lsa ham (masalan
            // ikkalasi ham sozlanmagan) belgilanadi — aks holda shu voqea
            // har ishga tushganda qayta-qayta tekshirilaveradi.
            $event->update(['notified_at' => now()]);
        }

        $this->info("Xodimga yuborilgan: {$sentToStaff}. HR guruhiga yuborilgan: {$sentToHr}. Jami voqea: {$events->count()}.");

        return self::SUCCESS;
    }
}
