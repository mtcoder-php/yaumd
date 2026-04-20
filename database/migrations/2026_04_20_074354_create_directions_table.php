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
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->string('hemis_code', 20)->unique()->nullable();
            $table->string('name_uz', 255);
            $table->string('name_ru', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->enum('degree', ['bachelor', 'master'])->default('bachelor');
            $table->tinyInteger('duration_years')->default(4);
            $table->integer('quota_grant')->default(0);
            $table->integer('quota_contract')->default(0);
            $table->decimal('annual_fee', 12, 2)->default(0);
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
        Schema::dropIfExists('directions');
    }
};
