<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title_uz', 255);
            $table->string('title_ru', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->text('description_uz')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_en')->nullable();
            $table->json('what_you_learn')->nullable();
            $table->json('requirements')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('promo_video')->nullable();
            $table->enum('type', ['open', 'free', 'paid', 'students_only'])->default('open');
            $table->enum('scorm_type', ['native', 'scorm12', 'scorm2004', 'xapi'])->default('native');
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->enum('language', ['uz', 'ru', 'en'])->default('uz');
            $table->enum('degree', ['bachelor', 'master', 'both'])->default('both');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->integer('duration_hours')->default(0);
            $table->boolean('has_certificate')->default(true);
            $table->boolean('is_sequential')->default(true);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('students_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
