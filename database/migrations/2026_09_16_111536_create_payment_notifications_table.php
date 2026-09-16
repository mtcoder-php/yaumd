<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Har bir kontrakt uchun Telegram bot orqali yuborilgan to'lov
 * eslatmasi/ogohlantirishini qayd qiladi — SendPaymentReminders (kunlik
 * ishga tushadigan buyruq) shu jadval orqali BIR XIL oy uchun bir xil
 * turdagi xabarni ikki marta yubormasligini ta'minlaydi (masalan "20-sana
 * yaqinlashyapti" eslatmasi bir oyda faqat bir marta yuboriladi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();

            // 'reminder_before' — muddatdan 5 kun oldin ogohlantirish;
            // 'deadline_today'  — muddat kuni (20-sana) hali to'lanmagan bo'lsa;
            // 'overdue'         — muddat o'tib ketgan (haftalik takrorlanadi);
            // 'payment_confirmed' — to'lov qabul qilingani haqida tasdiq.
            $table->string('type', 30);

            // Shu bildirishnoma tegishli bo'lgan oyning 1-sanasi — bir xil
            // (contract_id, type, period) kombinatsiyasi uchun faqat bitta
            // yozuv bo'lishi kerak (overdue esa period ichida haftalik
            // qayta yuborilishi mumkin, shuning uchun uning uchun alohida
            // "week" ustuni ham bor).
            $table->date('period');
            $table->unsignedTinyInteger('week')->default(0);

            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['contract_id', 'type', 'period', 'week'], 'payment_notifications_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
    }
};
