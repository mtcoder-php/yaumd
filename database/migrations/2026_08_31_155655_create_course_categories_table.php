<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
