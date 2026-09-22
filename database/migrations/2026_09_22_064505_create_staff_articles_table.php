<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Professor/o'qituvchi profil sahifasidagi "Maqolalar" tabi uchun —
 * har bir ilmiy maqola: sarlavha, qayerda chop etilgani, yili va
 * (mavjud bo'lsa) tashqi havola (DOI/PDF).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('title', 500);
            $table->string('source_uz', 255)->nullable(); // jurnal/nashr yoki konferentsiya nomi
            $table->string('year', 10)->nullable();
            $table->string('url', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_articles');
    }
};
