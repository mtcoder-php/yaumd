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
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('news_categories')->cascadeOnDelete();
            $table->string('slug', 500)->unique();
            $table->string('title_uz', 500);
            $table->string('title_ru', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->text('excerpt_uz')->nullable();
            $table->text('excerpt_ru')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('content_uz')->nullable();
            $table->longText('content_ru')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('image', 500)->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
