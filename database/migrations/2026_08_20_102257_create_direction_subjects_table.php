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
        Schema::create('direction_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->enum('block_type', ['mandatory', 'specialty_1', 'specialty_2']);
            $table->integer('questions_count')->default(10);
            $table->decimal('score_per_question', 4, 1)->default(1.1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['direction_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direction_subjects');
    }
};
