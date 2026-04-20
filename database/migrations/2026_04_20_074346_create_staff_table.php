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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('full_name_uz', 255);
            $table->string('full_name_ru', 255)->nullable();
            $table->string('full_name_en', 255)->nullable();
            $table->string('position_uz', 255);
            $table->string('position_ru', 255)->nullable();
            $table->string('position_en', 255)->nullable();
            $table->string('department_uz', 255)->nullable();
            $table->string('department_ru', 255)->nullable();
            $table->string('department_en', 255)->nullable();
            $table->text('bio_uz')->nullable();
            $table->text('bio_ru')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('photo', 500)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('reception_hours', 255)->nullable();
            $table->enum('type', ['leadership', 'teacher', 'staff'])->default('staff');
            $table->integer('order')->default(0);
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
        Schema::dropIfExists('staff');
    }
};
