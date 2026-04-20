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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_uz', 500)->nullable();
            $table->string('title_ru', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->text('description_uz')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('link', 500)->nullable();
            $table->string('button_text_uz', 100)->nullable();
            $table->string('button_text_ru', 100)->nullable();
            $table->string('button_text_en', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
