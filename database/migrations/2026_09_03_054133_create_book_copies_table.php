<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Har bir fizik kitob nusxasi (inventar birligi) shu jadvalda alohida
     * qatorga ega bo'ladi — bitta bibliografik yozuv (library_books)
     * bir nechta fizik nusxaga ega bo'lishi mumkin.
     *
     * 'status' enum'iga 'loaned' qiymati ham qo'shilgan — Phase 1 (shu
     * migratsiya) hali abonement/qaytarish funksiyasini yozmaydi, lekin
     * keyingi bosqichda (kitob berish/qaytarish) shu ustunning ustiga
     * qo'shimcha migratsiyasiz ishlash uchun oldindan tayyorlab qo'yilgan.
     */
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('inventory_code', 50)->unique();
            $table->enum('status', ['available', 'loaned', 'damaged', 'lost'])->default('available');
            $table->text('condition_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
