<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Xodimga "kech qoldingiz" / "erta ketdingiz" xabari kuniga har turdan
 * FAQAT BIR MARTA yuborilishini ta'minlaydi — 'payment_notifications'
 * jadvali bilan bir xil naqsh (NotifyStaffAttendance buyrug'i har necha
 * marta ishga tushirilsa ham, bir xil kun+tur uchun ikkinchi marta
 * yubormaydi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 10); // 'late' | 'early'
            $table->unsignedInteger('minutes');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['user_id', 'date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_notifications');
    }
};
