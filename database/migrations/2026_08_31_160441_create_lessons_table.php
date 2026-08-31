<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title_uz', 255);
            $table->string('title_ru', 255)->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'pdf', 'text', 'quiz', 'assignment', 'scorm'])->default('video');
            $table->integer('order')->default(0);
            $table->integer('duration')->default(0); // daqiqa
            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(true);
            $table->longText('content')->nullable();
            $table->foreignId('scorm_package_id')->nullable()->constrained('scorm_packages')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
