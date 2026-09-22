<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil sahifasidagi "Ish tajribasi" vaqt chizig'i — staff_educations
 * bilan bir xil (davr + sarlavha + izoh) shakl, lekin alohida jadval,
 * chunki ma'no jihatdan boshqa toifadagi ma'lumot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('period', 50); // masalan "2020 – hozir"
            $table->string('title', 255); // qalin qatordagi matn (odatda tashkilot)
            $table->string('subtitle', 255)->nullable(); // kichik kulrang qator (lavozim)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_experiences');
    }
};
