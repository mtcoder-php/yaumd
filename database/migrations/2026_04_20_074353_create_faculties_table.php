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
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 255);
            $table->string('name_ru', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->string('short_name', 20)->nullable();
            $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description_uz')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image', 500)->nullable();
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
        Schema::dropIfExists('faculties');
    }
};
