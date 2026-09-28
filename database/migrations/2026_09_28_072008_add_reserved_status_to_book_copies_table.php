<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kitob rezervatsiya (band qilish) tizimi uchun — nusxa qaytarilganda
 * navbatdagi shaxs uchun "ushlab turilgan" holatini ifodalash kerak.
 * Bu ATAYLAB 'loaned'dan ALOHIDA yangi holat: 'reserved' bo'lgan nusxa
 * hali hech kimga berilmagan (BookLoan yozuvi yo'q), faqat ma'lum bir
 * shaxs uchun band qilib qo'yilgan va oddiy "bo'sh" nusxalar ro'yxatida
 * (available_copies_count) ko'rinmasligi kerak.
 *
 * MUHIM: Laravel'ning Schema::table(...)->change() metodi ENUM ustunlar
 * uchun 'doctrine/dbal' paketini talab qiladi (loyihada o'rnatilmagan),
 * shuning uchun bu yerda to'g'ridan-to'g'ri xom SQL orqali o'zgartiriladi
 * (MySQL-ga xos, lekin loyiha allaqachon faqat MySQL bilan ishlaydi).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE book_copies MODIFY status ENUM('available','loaned','reserved','damaged','lost') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        // Orqaga qaytarishdan oldin 'reserved' holatidagi qatorlarni
        // 'available'ga o'tkazamiz — aks holda ENUM'dan qiymat olib
        // tashlanganda MySQL ularni bo'sh qatorga aylantirib qo'yadi.
        DB::table('book_copies')->where('status', 'reserved')->update(['status' => 'available']);
        DB::statement("ALTER TABLE book_copies MODIFY status ENUM('available','loaned','damaged','lost') NOT NULL DEFAULT 'available'");
    }
};
