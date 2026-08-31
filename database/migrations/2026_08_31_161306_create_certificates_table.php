<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_number', 50)->unique();
            $table->string('pdf_path', 500)->nullable();
            $table->string('qr_code', 500)->nullable();
            $table->decimal('final_score', 5, 2)->default(0);
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
