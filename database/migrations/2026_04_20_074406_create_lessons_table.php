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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'document', 'scorm', 'text'])->default('text');
            $table->string('file_path', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->integer('order')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
