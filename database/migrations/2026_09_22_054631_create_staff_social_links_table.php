<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil sahifasidagi "Ijtimoiy tarmoqlar" kartasi — platforma turi
 * (telegram/linkedin/google_scholar/researchgate/website/...) frontendda
 * mos icon tanlash uchun, 'label' esa ko'rsatiladigan matn
 * (masalan "@azizbek_rakhmatov" yoki "Google Scholar").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 30);
            $table->string('label', 255);
            $table->string('url', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_social_links');
    }
};
