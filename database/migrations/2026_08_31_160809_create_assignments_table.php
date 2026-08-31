<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title_uz', 255);
            $table->string('title_ru', 255)->nullable();
            $table->longText('description_uz');
            $table->longText('description_ru')->nullable();
            $table->integer('max_score')->default(100);
            $table->integer('passing_score')->default(60);
            $table->timestamp('deadline')->nullable();
            $table->boolean('allow_late')->default(false);
            $table->boolean('allow_resubmit')->default(true);
            $table->integer('max_attempts')->default(3);
            $table->enum('submission_type', ['file', 'text', 'both'])->default('both');
            $table->json('allowed_file_types')->nullable(); // ['pdf','docx','zip']
            $table->integer('max_file_size')->default(10); // MB
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
