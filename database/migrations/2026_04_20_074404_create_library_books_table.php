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
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('library_categories')->cascadeOnDelete();
            $table->string('isbn', 20)->unique()->nullable();
            $table->string('title', 500);
            $table->string('author', 255);
            $table->string('publisher', 255)->nullable();
            $table->year('published_year')->nullable();
            $table->string('language', 50)->default('uz');
            $table->text('description')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->enum('access_type', ['free', 'paid', 'subscription'])->default('free');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
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
        Schema::dropIfExists('library_books');
    }
};
