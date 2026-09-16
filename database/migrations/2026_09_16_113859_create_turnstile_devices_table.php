<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Har bir Hikvision Face ID terminalini (kirish/chiqish nuqtasi) tavsiflaydi.
 * Bularning ro'yxati va portlari Mukhtor tomonidan berilgan haqiqiy
 * inventarizatsiyaga asoslanadi (TurnstileDeviceSeeder'ga qarang).
 *
 * MUHIM: bu yerda parol saqlanmaydi — barcha 12 ta terminalda bir xil
 * admin login/parol ishlatilgani sababli, u markaziy config/services.php
 * ('hikvision' bo'limi) orqali .env'dan o'qiladi. Agar kelajakda
 * terminallarning parollari har xil bo'lib qolsa, shu jadvalga
 * 'username'/'password' (encrypted cast bilan) ustunlarini qo'shish kerak
 * bo'ladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnstile_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // masalan: "chiqish-5", "kirganda-ong-taraf-2"
            $table->string('scheme', 10)->default('http');
            $table->string('host'); // hozircha tashqi manzil (90.156.195.197) ishlatilmoqda,
            // chunki YAUMD serveri qayerda joylashishi hali aniq emas — agar
            // kelajakda YAUMD terminallar bilan bitta ichki tarmoqda (masalan
            // 192.168.91.x) ishga tushsa, shu ustunni o'sha ichki IP'ga
            // almashtirish kifoya, kod o'zgarmaydi.
            $table->unsignedInteger('port');
            // 'kirish' yoki 'chiqish' — nomidan avtomatik aniqlanadi
            // (TurnstileDeviceSeeder), lekin qo'lda ham o'zgartirish mumkin.
            $table->string('direction', 10)->nullable();
            $table->boolean('is_active')->default(true);
            // Voqealarni so'rovda (AcsEvent) qaysi vaqtdan boshlab qidirish
            // kerakligini bildiruvchi "kursor" — har muvaffaqiyatli sinxronlashdan
            // keyin oxirgi ko'rilgan voqea vaqtiga yangilanadi.
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedBigInteger('last_serial_no')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['host', 'port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnstile_devices');
    }
};
