<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            // Kontrakt Abituriyentlar oqimi orqali (applicant_id) yoki talaba
            // to'g'ridan-to'g'ri kiritilganda/import qilinganda (student_id)
            // yaratilishi mumkin — shu sababli ikkalasi ham ixtiyoriy.
            // student_id uchun tashqi kalit cheklovi bu yerda qo'shilmaydi,
            // chunki "students" jadvali hali yaratilmagan (pastdagi
            // create_students_table migratsiyasida qo'shiladi).
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable();
            $table->foreignId('direction_id')->constrained();
            $table->string('contract_number', 30)->unique();
            // 'amount' — chegirma qo'llangandan keyingi, haqiqiy to'lanishi
            // kerak bo'lgan summa ("net"). 'base_amount' — chegirmasiz
            // to'liq narx ("gross"); chegirma bo'lmasa ikkalasi teng.
            // Talabalarga oilaviy sharoit, yetimlik, nogironlik, kam
            // ta'minlanganlik va boshqa sabablar bilan chegirma berish
            // imkoniyati uchun (ContractController shu ikkalasidan
            // 'amount'ni serverda hisoblaydi, mijozga ishonilmaydi).
            $table->decimal('amount', 12, 2);
            $table->decimal('base_amount', 12, 2);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->string('discount_reason', 30)->nullable();
            $table->string('discount_note', 500)->nullable();
            $table->enum('payment_type', ['grant', 'contract'])->default('contract');
            $table->enum('status', ['draft', 'signed', 'paid', 'cancelled'])->default('draft');
            $table->string('pdf_path', 500)->nullable();
            $table->string('qr_code', 500)->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
