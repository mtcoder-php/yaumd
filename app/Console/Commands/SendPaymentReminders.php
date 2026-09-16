<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\PaymentNotification;
use App\Models\Student;
use App\Services\ContractPaymentScheduleService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Har kuni ishga tushirilishi kerak bo'lgan buyruq (routes/console.php'da
 * Schedule::command orqali ro'yxatdan o'tkazilgan — serverda haqiqatda
 * ishlashi uchun cron'ga "* * * * * php artisan schedule:run" qatorini
 * qo'shish shart, buni Laravel'ning o'zi eslatadi).
 *
 * Har bir Telegram'ga ulangan, kontrakt asosida o'qiydigan talaba uchun:
 *   - muddatdan 5 kun oldin (15-sana) hali to'lanmagan bo'lsa — eslatma;
 *   - muddat kuni (20-sana) hali to'lanmagan bo'lsa — "bugun oxirgi kun";
 *   - muddatdan keyin (21-sanadan boshlab, haftada bir marta) hali
 *     to'lanmagan bo'lsa — "muddat o'tdi, turniketda muammo bo'ladi".
 *
 * Bir xil oy uchun bir xil turdagi xabar ikki marta yuborilmasligi
 * payment_notifications jadvali orqali ta'minlanadi.
 */
class SendPaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';

    protected $description = "Telegram botga ulangan talabalarga to'lov muddati eslatmalarini yuboradi";

    public function handle(ContractPaymentScheduleService $schedule, TelegramService $telegram): int
    {
        $today = Carbon::today();
        $sent = 0;

        $students = Student::whereNotNull('telegram_chat_id')
            ->where('funding_type', 'contract')
            ->where('status', 'active')
            ->get();

        foreach ($students as $student) {
            $contract = $schedule->findActiveContract($student);

            if (! $contract) {
                continue;
            }

            $status = $schedule->currentStatus($contract, $today);

            if (! $status['known'] || $status['is_fully_paid']) {
                continue;
            }

            // 1) O'TGAN (allaqachon muddati kelgan/o'tgan) davr uchun qarz
            // bo'lsa: aynan bugun (daysOverdue===0) bo'lsa — "bugun oxirgi
            // kun" (deadline_today); 21-sanadan boshlab (daysOverdue>0) —
            // haftada bir marta qayta yuboriladigan "overdue" xabari. Bu
            // davr uchun boshqa (reminder_before) xabar keraksiz, shuning
            // uchun shu yerda "continue" qilinadi.
            if (! $status['is_compliant'] && $status['current_deadline']) {
                $overdueDeadline = $status['current_deadline'];
                // MUHIM: Carbon::diffInDays() float qaytarishi mumkin —
                // pastdagi "daysOverdue === 0" solishtirish uchun (int)ga
                // o'tkazish shart.
                $daysOverdue = (int) $overdueDeadline->diffInDays($today);
                $period = $overdueDeadline->copy()->startOfMonth();

                if ($daysOverdue === 0) {
                    $sent += $this->notifyOnce(
                        $contract, PaymentNotification::TYPE_DEADLINE_TODAY, $period, 0,
                        $telegram, $student->telegram_chat_id,
                        "⚠️ Bugun ({$overdueDeadline->format('d.m.Y')}) to'lov muddatining oxirgi kuni! Qarz: ".
                        number_format($status['debt_amount'], 0, '.', ' ')." so'm."
                    );
                } else {
                    $week = (int) floor($daysOverdue / 7) + 1;

                    $sent += $this->notifyOnce(
                        $contract, PaymentNotification::TYPE_OVERDUE, $period, $week,
                        $telegram, $student->telegram_chat_id,
                        "❌ To'lov muddati ({$overdueDeadline->format('d.m.Y')}) o'tib ketgan. Qarz: ".
                        number_format($status['debt_amount'], 0, '.', ' ')." so'm. Turniketdan o'tishda muammo bo'lishi mumkin — iltimos, tezroq to'lovni amalga oshiring."
                    );
                }

                continue;
            }

            // 2) O'tgan muddat bo'yicha qarz yo'q (yoki hali birinchi muddat
            // kelmagan), lekin KELAYOTGAN muddatgacha yetarli to'lanmagan
            // bo'lsa — oldindan eslatma (5 kun oldin). ("Bugun oxirgi kun"
            // holati bu yerga yetib kelmaydi — chunki next_deadline har
            // doim FAQAT kelajakdagi sana bo'ladi (hisoblanish tartibiga
            // ko'ra), aynan bugungi muddat esa yuqoridagi 1-bo'limda
            // "o'tgan/joriy davr" sifatida allaqachon qayta ishlanadi.)
            if ($status['is_compliant_for_next'] || ! $status['next_deadline']) {
                continue;
            }

            $nextDeadline = $status['next_deadline'];
            // MUHIM: Carbon::diffInDays() PHP 8.1+da float qaytarishi mumkin
            // (masalan 5.0) — shu sababli quyida strict "=== 5" solishtirish
            // ishlashi uchun aniq (int) ga o'tkazish SHART (aks holda hech
            // qachon mos kelmay, eslatma umuman yuborilmaydi).
            $daysUntil = (int) $today->diffInDays($nextDeadline);
            $period = $nextDeadline->copy()->startOfMonth();
            $amountNeeded = round(max(0, $status['next_required_amount'] - $status['paid_amount']), 2);

            if ($daysUntil === 5) {
                $sent += $this->notifyOnce(
                    $contract, PaymentNotification::TYPE_REMINDER_BEFORE, $period, 0,
                    $telegram, $student->telegram_chat_id,
                    "⏰ Eslatma: {$nextDeadline->format('d.m.Y')} kuniga qadar ".
                    number_format($amountNeeded, 0, '.', ' ')." so'm to'lashingiz kerak, aks holda keyingi oy turniketdan o'tishda muammo bo'lishi mumkin."
                );
            }
        }

        $this->info("Yuborilgan bildirishnomalar: {$sent}");

        return self::SUCCESS;
    }

    private function notifyOnce(
        Contract $contract,
        string $type,
        Carbon $period,
        int $week,
        TelegramService $telegram,
        string $chatId,
        string $message,
    ): int {
        // MUHIM: 'period' ustuni 'date' cast qilingan bo'lsa-da, Eloquent
        // saqlashda standart model $dateFormat'ini ("Y-m-d H:i:s") ishlatadi
        // — ya'ni bazada haqiqatda "2026-09-01 00:00:00" kabi to'liq
        // vaqt bilan saqlanadi. Oddiy where('period', '2026-09-01') satr
        // taqqoslash bo'lgani uchun bunga mos kelmaydi va har safar
        // "topilmadi" deb noto'g'ri xulosa chiqarib, keyin UNIQUE cheklov
        // xatosiga olib kelardi. whereDate() esa haydovchidan qat'i nazar
        // faqat sana qismini solishtiradi — shu bilan to'g'ri ishlaydi.
        $existing = PaymentNotification::where('contract_id', $contract->id)
            ->where('type', $type)
            ->whereDate('period', $period->toDateString())
            ->where('week', $week)
            ->exists();

        if ($existing) {
            return 0;
        }

        $telegram->sendMessage($chatId, $message);

        PaymentNotification::create([
            'contract_id' => $contract->id,
            'type'        => $type,
            'period'      => $period,
            'week'        => $week,
            'sent_at'     => now(),
        ]);

        return 1;
    }
}
