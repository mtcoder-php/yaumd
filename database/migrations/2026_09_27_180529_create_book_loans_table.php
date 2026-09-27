<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kutubxona abonementi — kim (talaba yoki xodim) qaysi fizik nusxani
 * qachongacha olgani. 'book_copies.status' ustuni ("loaned") allaqachon
 * bor edi, lekin KIM va QACHONGACHA olgani hech qayerda saqlanmas edi —
 * shu jadval aynan shu bo'shliqni to'ldiradi.
 *
 * 'borrower_type'/'borrower_id' — PersonMatch/TurnstileEvent'dagi bilan
 * BIR XIL polimorfik naqsh ('student' => App\Models\Student, 'staff' =>
 * App\Models\User — AppServiceProvider'dagi Relation::morphMap()ga
 * qarang), chunki bu yerda ham talaba, ham xodim kitob olishi mumkin.
 *
 * MUHIM: 'status' ustuni ATAYLAB 'book_copies.status'dan MUSTAQIL — bitta
 * nusxa vaqt o'tishi bilan bir necha marta turli shaxslarga berilishi
 * mumkin, shuning uchun har bir "berish" o'z tarixiy yozuviga ega bo'lishi
 * kerak (nusxaning o'zidagi bitta ustunga yozib qo'yish tarixni yo'qotib
 * qo'yardi). "Muddati o'tganmi" (overdue) alohida ustunda SAQLANMAYDI —
 * bu har doim 'due_date < hozir VA status=active' orqali hisoblanadi
 * (ContractPaymentScheduleService'dagi "is_compliant" kabi — statik
 * saqlangan bayroq eskirib qolishi mumkin, hisoblangan qiymat esa hech
 * qachon eskirmaydi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_copy_id')->constrained('book_copies')->cascadeOnDelete();

            $table->string('borrower_type', 20); // 'student' | 'staff'
            $table->unsignedBigInteger('borrower_id');

            // Kim berdi / kim qabul qildi (kutubxonachi) — ikkalasi ham
            // 'users' jadvaliga ishora qiladi, chunki faqat xodimlar admin
            // panelda ishlaydi.
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('borrowed_at');
            $table->date('due_date');
            $table->timestamp('returned_at')->nullable();

            $table->enum('status', ['active', 'returned', 'lost'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['borrower_type', 'borrower_id']);
            $table->index('due_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
