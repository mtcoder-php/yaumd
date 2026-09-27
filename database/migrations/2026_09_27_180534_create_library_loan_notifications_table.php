<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'attendance_notifications'/'payment_notifications' bilan BIR XIL
 * maqsad — bitta kitob abonementi uchun bitta turdagi Telegram xabari
 * ikki marta yuborilib ketmasligini ta'minlaydi (NotifyLibraryLoans
 * buyrug'iga qarang).
 *
 * 'week' ustuni FAQAT 'overdue' turi uchun ma'noli — 'reminder_before'da
 * har doim 0 (chunki u FAQAT bir marta, muddat yaqinlashganda yuboriladi);
 * muddat o'tgach esa har hafta bittadan qayta yuborilishi kerak
 * (SendPaymentReminders'dagi "haftalik overdue" naqshi bilan bir xil).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_loan_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_loan_id')->constrained('book_loans')->cascadeOnDelete();
            $table->string('type', 20); // 'reminder_before' | 'overdue'
            $table->unsignedInteger('week')->default(0);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['book_loan_id', 'type', 'week'], 'library_loan_notifications_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_loan_notifications');
    }
};
