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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('provider', ['click', 'payme', 'cash'])->default('cash');
            $table->string('transaction_id', 255)->unique()->nullable();
            // 'cancelled' — Click/Payme orqali onlayn to'lov bekor
            // qilinganda ClickCallbackController/PaymeCallbackController
            // aynan shu qiymatni yozadi (payment_orders jadvalidagi bilan
            // bir xil status to'plami uchun) — shuning uchun bu yerda ham
            // bo'lishi shart, 'refunded' esa moliya xodimi 'cash' to'lovni
            // qo'lda qaytarganda ishlatiladi.
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->json('provider_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedTinyInteger('cancel_reason')->nullable();
            $table->unsignedBigInteger('payme_create_time')->nullable();
            $table->unsignedBigInteger('payme_perform_time')->nullable();
            $table->unsignedBigInteger('payme_cancel_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
