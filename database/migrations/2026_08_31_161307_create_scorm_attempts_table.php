<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorm_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('attempt_id', 100)->unique();
            $table->enum('completion_status', ['unknown', 'incomplete', 'completed', 'not_attempted'])->default('not_attempted');
            $table->enum('success_status', ['unknown', 'passed', 'failed'])->default('unknown');
            $table->decimal('score_raw', 8, 2)->nullable();
            $table->decimal('score_min', 8, 2)->nullable();
            $table->decimal('score_max', 8, 2)->nullable();
            $table->decimal('score_scaled', 5, 4)->nullable();
            $table->integer('session_time')->default(0); // sekund
            $table->integer('total_time')->default(0);   // sekund
            $table->json('suspend_data')->nullable();     // SCORM suspend data
            $table->json('interactions')->nullable();     // SCORM interactions
            $table->json('objectives')->nullable();       // SCORM objectives
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_attempts');
    }
};
