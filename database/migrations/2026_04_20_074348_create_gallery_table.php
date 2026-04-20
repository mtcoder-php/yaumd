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
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('title_uz', 500)->nullable();
            $table->string('title_ru', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->string('file', 500);
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('thumbnail', 500)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery');
    }
};
