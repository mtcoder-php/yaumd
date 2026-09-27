<?php

namespace App\Console\Commands;

use App\Models\BookLoan;
use App\Models\LibraryLoanNotification;
use App\Models\Student;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Har kuni ishga tushiriladigan buyruq — Telegram botga ulangan har bir
 * FAOL kitob abonementi (talaba ham, xodim ham) uchun:
 *
 *   - muddat tugashiga 1-2 kun qolganda — bir marta oldindan eslatma;
 *   - muddat o'tib ketgan bo'lsa — muddat o'tgan kundan boshlab HAR
 *     HAFTA bittadan qayta eslatma (SendPaymentReminders'dagi "haftalik
 *     overdue" naqshi bilan bir xil).
 *
 * Bir xil abonement uchun bir xil turdagi xabar ikki marta yuborilmasligi
 * 'library_loan_notifications' jadvali orqali ta'minlanadi.
 */
class NotifyLibraryLoans extends Command
{
    protected $signature = 'library:notify-loans';

    protected $description = "Telegram botga ulangan talaba/xodimlarga kitob qaytarish muddati haqida eslatma yuboradi";

    public function handle(TelegramService $telegram): int
    {
        $today = Carbon::today();
        $sent = 0;

        $loans = BookLoan::query()
            ->where('status', BookLoan::STATUS_ACTIVE)
            ->with(['bookCopy.book', 'borrower'])
            ->get();

        foreach ($loans as $loan) {
            $borrower = $loan->borrower;

            if (! $borrower instanceof Student && ! $borrower instanceof User) {
                continue;
            }

            $chatId = $borrower->telegram_chat_id;

            if (! $chatId) {
                continue;
            }

            $bookTitle = $loan->bookCopy?->book?->title ?? "kitob";
            // MUHIM: 'due_date' 'date' cast qilingan Carbon — lekin kunlar
            // farqini SendPaymentReminders'dagi bilan bir xil sababdan
            // (diffInDays PHP 8.1+da float qaytarishi mumkin) (int)ga
            // majburan o'tkazamiz, aks holda strict solishtirish ishlamay
            // qolishi mumkin.
            $daysUntilDue = (int) $today->diffInDays($loan->due_date, false);

            if ($daysUntilDue < 0) {
                // Muddat o'tgan — haftada bir marta qayta eslatma.
                $daysOverdue = abs($daysUntilDue);
                $week = (int) floor($daysOverdue / 7) + 1;

                $sent += $this->notifyOnce(
                    $loan, LibraryLoanNotification::TYPE_OVERDUE, $week, $telegram, $chatId,
                    "📕 <b>{$bookTitle}</b> kitobini qaytarish muddati ({$loan->due_date->format('d.m.Y')}) o'tib ketgan — {$daysOverdue} kun kechikdingiz. Iltimos, kitobni tezroq kutubxonaga qaytaring."
                );
            } elseif ($daysUntilDue <= 2) {
                // 0 (bugun), 1 yoki 2 kun qoldi — bittasi kifoya, shuning
                // uchun (week=0) faqat BIR MARTA yuboriladi (qaysi kuni
                // ishga tushirilgan bo'lsa ham, shu oyna ichida ikkinchi
                // marta yuborilmaydi).
                $whenText = $daysUntilDue === 0
                    ? 'bugun'
                    : "{$daysUntilDue} kundan keyin";

                $sent += $this->notifyOnce(
                    $loan, LibraryLoanNotification::TYPE_REMINDER_BEFORE, 0, $telegram, $chatId,
                    "📗 Eslatma: <b>{$bookTitle}</b> kitobini {$loan->due_date->format('d.m.Y')} kuniga ({$whenText}) qadar qaytarishingiz kerak."
                );
            }
        }

        $this->info("Yuborilgan bildirishnomalar: {$sent}");

        return self::SUCCESS;
    }

    private function notifyOnce(
        BookLoan $loan,
        string $type,
        int $week,
        TelegramService $telegram,
        string $chatId,
        string $message,
    ): int {
        $exists = LibraryLoanNotification::where('book_loan_id', $loan->id)
            ->where('type', $type)
            ->where('week', $week)
            ->exists();

        if ($exists) {
            return 0;
        }

        $telegram->sendMessage($chatId, $message);

        LibraryLoanNotification::create([
            'book_loan_id' => $loan->id,
            'type' => $type,
            'week' => $week,
            'sent_at' => now(),
        ]);

        return 1;
    }
}
