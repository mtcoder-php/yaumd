<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\TurnstileEvent;
use App\Services\ContractPaymentScheduleService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

/**
 * Talaba turniketdan o'tgan (kirdi/chiqdi) HAR bir voqea uchun, agar u
 * Telegram botga ulangan bo'lsa, darhol qisqa xabar yuboradi: soat necha
 * bo'lganda, qaysi tomondan o'tgani, va bu oy uchun to'lov holati (qarz
 * bormi, turniket muammosiz ochiladimi).
 *
 * MUHIM: bu buyruq 'turnstile:sync-events' (voqeani bazaga yozadi) va
 * 'turnstile:match-people' (voqeani talaba/xodim yozuviga bog'laydi)dan
 * KEYIN ishga tushishi kerak (routes/console.php'dagi tartibga qarang) —
 * faqat ALLAQACHON 'student'ga moslashtirilgan va hali xabar
 * yuborilmagan ('notified_at' bo'sh) voqealarni ko'radi. Har bir voqea
 * ko'rib chiqilgach (xabar yuborilgan-yubormaganidan qat'i nazar)
 * 'notified_at' belgilanadi — shu sababli hech qachon ikki marta
 * yuborilmaydi va abadiy qayta urinilmaydi.
 */
class NotifyStudentTurnstileEvents extends Command
{
    protected $signature = 'turnstile:notify-students {--limit=200}';

    protected $description = "Telegram botga ulangan talabalarga har bir turniket (kirish/chiqish) voqeasi haqida darhol xabar yuboradi";

    public function handle(TelegramService $telegram, ContractPaymentScheduleService $schedule): int
    {
        $events = TurnstileEvent::query()
            ->whereNull('notified_at')
            ->where('matched_type', 'student')
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

        $sent = 0;

        foreach ($events as $event) {
            $student = Student::find($event->matched_id);

            if ($student && $student->telegram_chat_id) {
                $telegram->sendMessage(
                    $student->telegram_chat_id,
                    $this->composeMessage($event, $student, $schedule)
                );
                $sent++;
            }

            // Yuborilmagan bo'lsa ham (masalan hali botga ulanmagan
            // talaba) belgilanadi — aks holda shu voqea har ishga
            // tushganda qayta-qayta tekshirilaveradi.
            $event->update(['notified_at' => now()]);
        }

        $this->info("Yuborilgan xabarlar: {$sent}/{$events->count()}.");

        return self::SUCCESS;
    }

    private function composeMessage(TurnstileEvent $event, Student $student, ContractPaymentScheduleService $schedule): string
    {
        $time = $event->event_time->format('H:i');
        $direction = match ($event->device?->direction) {
            'kirish' => 'kirdingiz',
            'chiqish' => 'chiqdingiz',
            default => "o'tdingiz",
        };
        $icon = $event->device?->direction === 'chiqish' ? '🚶' : '🚪';

        $lines = ["{$icon} Siz bugun soat <b>{$time}</b> da {$direction}."];

        if ($student->funding_type === 'grant') {
            $lines[] = '🎓 Grant asosida o\'qiysiz — kontrakt to\'lovi talab qilinmaydi.';

            return implode("\n\n", $lines);
        }

        $contract = $schedule->findActiveContract($student);

        if (! $contract) {
            return implode("\n\n", $lines);
        }

        $status = $schedule->currentStatus($contract);

        if (! $status['known']) {
            $lines[] = "⚠️ Guruh/o'quv yili ma'lumoti to'liq emas — aniq holatni admin bilan tekshiring.";
        } elseif ($status['is_fully_paid']) {
            $lines[] = '✅ Kontraktingiz to\'liq to\'langan.';
        } elseif ($status['is_compliant']) {
            $lines[] = "✅ Bu oy uchun to'lov muammosi yo'q, turniketda muammo bo'lmaydi.";
        } else {
            $debt = number_format($status['debt_amount'], 0, '.', ' ');
            $lines[] = "❌ Qarz: <b>{$debt} so'm</b> — turniketdan o'tishda muammo bo'lishi mumkin.";
        }

        return implode("\n\n", $lines);
    }
}
