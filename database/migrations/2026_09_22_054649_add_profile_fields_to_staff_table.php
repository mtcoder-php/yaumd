<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Professor profil sahifasi ("Umumiy ma'lumot" tabi) uchun qo'shimcha
 * maydonlar: statistika kartalari (yillik tajriba, talabalar, ilmiy
 * maqola, loyiha soni), qisqa joylashuv matni, "Ilmiy faoliyati" bo'limi
 * uchun alohida qisqa matn (bio_uz'dan farqli — bio_uz umumiy tarjimai
 * hol, research_summary_uz esa faqat ilmiy faoliyat haqida) va CV fayli
 * yo'li ("CV yuklab olish" tugmasi uchun, hozircha bo'sh bo'lishi mumkin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedSmallInteger('experience_years')->nullable()->after('research_tags');
            $table->unsignedInteger('students_count')->nullable()->after('experience_years');
            $table->unsignedInteger('articles_count')->nullable()->after('students_count');
            $table->unsignedInteger('projects_count')->nullable()->after('articles_count');
            $table->string('location', 255)->nullable()->after('projects_count');
            $table->text('research_summary_uz')->nullable()->after('location');
            $table->string('cv_file', 500)->nullable()->after('research_summary_uz');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'experience_years', 'students_count', 'articles_count',
                'projects_count', 'location', 'research_summary_uz', 'cv_file',
            ]);
        });
    }
};
