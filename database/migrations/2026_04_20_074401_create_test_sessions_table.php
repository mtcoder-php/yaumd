<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('direction_id')->constrained()->cascadeOnDelete();
            $table->enum('language', ['uz', 'ru'])->default('uz');
            $table->enum('foreign_lang', ['en', 'ar'])->default('en');
            $table->string('login', 50)->unique();
            $table->string('password_plain', 20);
            $table->string('password', 255);
            $table->decimal('score', 6, 1)->nullable();
            $table->integer('correct_answers')->nullable();
            $table->integer('total_questions')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'expired'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('answers')->nullable();
            $table->json('questions')->nullable(); // ← savollar bir marta saqlanadi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_sessions');
    }
};
