<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pullik narsalarni (elektron kitob, pullik kurs va kelajakda
     * qo'shilishi mumkin bo'lgan boshqa turlar) onlayn Click/Payme orqali
     * sotib olish uchun UMUMIY to'lov buyurtmasi jadvali. 'payable_type' +
     * 'payable_id' polimorfik bog'lanish orqali istalgan modelga (hozircha
     * LibraryBook va Course) ishora qiladi — shu sababli Click/Payme
     * protokoli kodi (ClickPaymentService, PaymePaymentService,
     * ClickCallbackController, PaymeCallbackController) FAQAT BIR MARTA
     * yoziladi va ikkalasi uchun ham ishlatiladi.
     *
     * Mavjud 'payments' jadvalidan ATAYLAB alohida qilingan — 'payments'
     * faqat 'contracts' (kontrakt/o'quv to'lovi) bilan bog'liq va uning
     * contract_id ustuni MAJBURIY, shu sababli uni qayta ishlatish moliya
     * (Kontraktlar) bo'limidagi ishlayotgan kodni buzish xavfini keltirib
     * chiqarardi.
     */
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('provider', ['click', 'payme'])->default('click');
            $table->string('transaction_id', 255)->nullable()->unique();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->json('provider_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            // Payme protokoli talab qiladigan qo'shimcha holat maydonlari
            // (bekor qilingan vaqti va sababi — Payme'ning CancelTransaction
            // metodi uchun, va CreateTransaction/PerformTransaction/
            // CancelTransaction javoblarida qaytarilishi shart bo'lgan
            // millisekundli vaqt belgilari — bular Payme tomonidan
            // beriladi, har safar bir xil qaytarilishi kerak)
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedTinyInteger('cancel_reason')->nullable();
            $table->unsignedBigInteger('payme_create_time')->nullable();
            $table->unsignedBigInteger('payme_perform_time')->nullable();
            $table->unsignedBigInteger('payme_cancel_time')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
