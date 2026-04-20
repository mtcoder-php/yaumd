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
        Schema::create('test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('login', 50)->unique();
            $table->string('password', 255);
            $table->integer('score')->nullable();
            $table->integer('correct_answers')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'expired'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sessions');
    }
};
