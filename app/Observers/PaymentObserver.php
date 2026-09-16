<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\ContractPaymentScheduleService;
use App\Services\TelegramService;

/**
 * To'lov "to'landi" (paid) holatiga o'tganda (kassir qo'lda kiritganda,
 * yoki Click/Payme callback tasdiqlaganda) talabaga darhol Telegram
 * orqali tasdiq xabari yuboradi — kunlik SendPaymentReminders buyrug'ini
 * kutmasdan.
 *
 * MUHIM: bu observer HECH QACHON to'lov jarayonining o'zini
 * to'xtatmasligi kerak — Telegram API bilan bog'liq har qanday xatolik
 * (tarmoq, noto'g'ri token va h.k.) shu yerdayoq ushlab qolinadi
 * (TelegramService o'zi ham xatoni "yutadi", lekin qo'shimcha ehtiyot
 * uchun bu yerda ham try/catch bor).
 */
class PaymentObserver
{
    public function __construct(
        private TelegramService $telegram,
        private ContractPaymentScheduleService $schedule,
    ) {
    }

    public function created(Payment $payment): void
    {
        if ($payment->status === 'paid') {
            $this->notify($payment);
        }
    }

    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('status') && $payment->status === 'paid') {
            $this->notify($payment);
        }
    }

    private function notify(Payment $payment): void
    {
        try {
            $contract = $payment->contract;

            if (! $contract) {
                return;
            }

            $student = $this->schedule->studentForContract($contract);

            if (! $student || ! $student->telegram_chat_id) {
                return;
            }

            $status = $this->schedule->currentStatus($contract);
            $fmt = fn (float $v) => number_format($v, 0, '.', ' ') . " so'm";

            $lines = ["✅ To'lovingiz qabul qilindi: <b>" . $fmt((float) $payment->amount) . "</b>."];

            if ($status['is_fully_paid']) {
                $lines[] = "Kontraktingiz to'liq to'landi. Rahmat!";
            } elseif ($status['known'] && $status['is_compliant']) {
                $lines[] = "Bu oy uchun to'lov talabi qondirildi — turniketda muammo bo'lmaydi.";
            }

            $this->telegram->sendMessage($student->telegram_chat_id, implode("\n", $lines));

            // Shu to'lov endi muvofiqlikni ta'minlagan bo'lsa, shu oy uchun
            // "overdue"/"deadline_today" eslatmalari endi keraksiz —
            // qo'shimcha spam bo'lmasligi uchun ularni belgilab qo'yamiz
            // emas, shunchaki keyingi kunlik buyruq allaqachon to'langanini
            // ko'rib avtomatik to'xtatadi (is_compliant tekshiruvi orqali).
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
