<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Professor/o'qituvchi profil sahifasidagi "Loyiha va dasturlar" tabi
 * uchun — har bir ilmiy/amaliy loyiha: sarlavha, qisqa tavsif, davri,
 * loyihadagi roli va (mavjud bo'lsa) tashqi havola.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description_uz')->nullable();
            $table->string('period', 50)->nullable(); // masalan "2021 – 2023"
            $table->string('role_uz', 255)->nullable(); // masalan "Ilmiy rahbar"
            $table->string('url', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_projects');
    }
};
