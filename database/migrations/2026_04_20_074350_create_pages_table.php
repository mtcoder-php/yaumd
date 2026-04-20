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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->unique();
            $table->string('title_uz', 500);
            $table->string('title_ru', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->longText('content_uz')->nullable();
            $table->longText('content_ru')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('meta_title', 500)->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
