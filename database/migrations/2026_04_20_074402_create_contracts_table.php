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
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('direction_id')->constrained();
            $table->string('contract_number', 30)->unique();
            $table->decimal('amount', 12, 2);
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
