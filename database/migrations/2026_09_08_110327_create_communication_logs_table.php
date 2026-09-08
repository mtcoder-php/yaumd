<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CRM — abituriyent/talaba bilan bo'lgan muloqot tarixi (qo'ng'iroq,
     * email, uchrashuv, eslatma). 'subject_type' + 'subject_id' —
     * polimorfik bog'lanish (App\Models\PaymentOrder'dagi 'payable_type'/
     * 'payable_id' bilan bir xil naqsh), App\Models\Student yoki
     * App\Models\Applicant'ga ishora qiladi.
     */
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->enum('type', ['call', 'email', 'meeting', 'note'])->default('call');
            $table->enum('direction', ['incoming', 'outgoing'])->nullable();
            $table->text('summary');
            $table->timestamp('occurred_at');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
