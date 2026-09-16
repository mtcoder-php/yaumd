<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Terminaldan ISAPI AcsEvent so'rovi orqali olingan har bir xom voqea shu
 * yerda saqlanadi. Bu jadval "yo'qlama" (attendance) modulining poydevori
 * bo'ladi, shuningdek talaba/xodimni terminaldagi employeeNoString bilan
 * moslashtirish (keyingi bosqich) ham shu yozuvlar ustida ishlaydi.
 *
 * 'turnstile_device_id' + 'serial_no' birgalikda UNIKAL — bu terminalning
 * o'z ichki ketma-ket raqami, shu sababli bir xil voqea ikki marta (masalan
 * qo'shni sahifalar chegarasida) qayta so'ralib qolsa ham, ikki marta
 * yozilmaydi (SyncTurnstileEvents firstOrCreate ishlatadi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnstile_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turnstile_device_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('serial_no');
            $table->timestamp('event_time');
            $table->unsignedSmallInteger('major');
            $table->unsignedSmallInteger('minor');
            // employeeNoString terminaldan qanday kelsa, xuddi shundayligicha
            // (masalan "s6236" yoki "u596") — bu ID YAUMD'ning o'z
            // student_id/staff_id'siga TENG EMAS (boshqa tizim tomonidan
            // tayinlangan), shuning uchun moslashtirish alohida bosqichda
            // ('matched_type'/'matched_id') amalga oshiriladi.
            $table->string('employee_no')->nullable();
            $table->string('person_name')->nullable();
            $table->unsignedTinyInteger('door_no')->nullable();
            $table->unsignedTinyInteger('card_reader_no')->nullable();
            $table->string('verify_mode')->nullable();
            $table->string('picture_url')->nullable();
            // Terminaldan kelgan JSON'ning to'liq nusxasi — hech narsa
            // yo'qolmasligi va keyinchalik parsing mantig'ini xatosiz qayta
            // tekshirish imkoni uchun.
            $table->json('raw_payload');

            // Talaba/xodim bilan bog'lanish — hozircha bo'sh, ism bo'yicha
            // moslashtirish tugallangach to'ldiriladi.
            $table->string('matched_type', 20)->nullable(); // 'student' | 'staff'
            $table->unsignedBigInteger('matched_id')->nullable();

            $table->timestamps();

            $table->unique(['turnstile_device_id', 'serial_no']);
            $table->index('employee_no');
            $table->index('event_time');
            $table->index(['major', 'minor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnstile_events');
    }
};
