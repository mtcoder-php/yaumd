<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kitob band qilish (rezervatsiya) navbati — barcha nusxalar band bo'lgan
 * kitobga talaba/xodim "navbatga tursin", nusxa bo'shaganda esa navbatdagi
 * BIRINCHI shaxs avtomatik xabardor qilinsin (BookLoan/BookCopy naqshi
 * bilan bir xil: polimorfik borrower_type/borrower_id, morphMap orqali
 * 'student' => Student, 'staff' => User).
 *
 * Holatlar ('status'):
 *   - waiting   — navbatda turibdi, hali unga hech qaysi nusxa ajratilmagan.
 *   - ready     — bir nusxa AYNAN shu shaxs uchun ajratilgan (book_copy_id
 *                 to'ldirilgan, BookCopy.status='reserved'), Telegram orqali
 *                 xabar berilgan va 'expires_at'gacha kelib olishi kerak.
 *   - fulfilled — kelib oldi, oddiy BookLoan yozuviga aylandi
 *                 (fulfilled_loan_id orqali bog'lanadi).
 *   - cancelled — shaxsning o'zi yoki kutubxonachi bekor qildi (shu
 *                 jumladan kutubxonachi nusxani boshqasiga berib
 *                 "override" qilgan holat ham shu yerga tushadi).
 *   - expired   — 'ready' holatida muddat o'tib ketdi (kelib olmadi),
 *                 navbat avtomatik keyingi kishiga o'tadi.
 *
 * Navbatdagi ANIQ o'rin alohida ustunda SAQLANMAYDI — BookLoan.isOverdue()
 * bilan bir xil mantiq: har doim 'created_at' bo'yicha, shu paytdagi
 * 'waiting' yozuvlar orasida hisoblanadi (statik raqam eskirib qolishi
 * mumkin, aks holda har bir bekor qilish/o'tishda qayta hisoblash kerak
 * bo'lar edi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->cascadeOnDelete();

            $table->string('borrower_type', 20); // 'student' | 'staff'
            $table->unsignedBigInteger('borrower_id');

            // Faqat 'ready' holatga o'tganda to'ldiriladi — aynan qaysi
            // fizik nusxa shu shaxs uchun ushlab turilgani.
            $table->foreignId('book_copy_id')->nullable()->constrained('book_copies')->nullOnDelete();

            $table->enum('status', ['waiting', 'ready', 'fulfilled', 'cancelled', 'expired'])->default('waiting');

            // 'ready'ga o'tgan payt va kelib olish uchun muddat (odatda
            // notified_at + Setting('library.reservation_pickup_hours')).
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Kelib olib, haqiqiy abonementga aylangan bo'lsa — shu yerga.
            $table->foreignId('fulfilled_loan_id')->nullable()->constrained('book_loans')->nullOnDelete();

            // Kim tomonidan va nima sababdan bekor qilingani (talabaning
            // o'zi bekor qilgan bo'lsa — null).
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancel_reason', 30)->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['borrower_type', 'borrower_id']);
            $table->index(['book_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};
