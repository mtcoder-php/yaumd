<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Professor/o'qituvchi profil sahifasidagi "Ta'lim va malaka" vaqt
 * chizig'i uchun — har bir yozuv erkin ko'rinishda (davr + sarlavha +
 * kichik izoh), chunki turli yozuvlarda "sarlavha" turlicha ma'no
 * anglatishi mumkin (ba'zida muassasa nomi, ba'zida daraja nomi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('period', 50); // masalan "2015 – 2018" yoki "2023"
            $table->string('title', 255); // qalin qatordagi matn
            $table->string('subtitle', 255)->nullable(); // kichik kulrang qator
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_educations');
    }
};
