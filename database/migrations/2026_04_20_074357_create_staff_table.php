<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MUHIM: bu migratsiya avval (074346 vaqtida) 'faculties'/'departments'
     * jadvallaridan OLDIN ishga tushardi — shuning uchun 'faculty_id'/
     * 'department_id' tashqi kalitlarini o'shanda to'g'ridan-to'g'ri shu
     * yerga qo'shib bo'lmasdi (ular hali mavjud bo'lmagan jadvallarga
     * ishora qilardi). Endi bu migratsiya ataylab 074357 vaqtiga
     * ko'chirildi — 'faculties' (074353) va 'departments' (074355)dan
     * KEYIN ishga tushadi, shuning uchun tashqi kalitlar endi to'g'ridan-
     * to'g'ri shu asosiy jadval yaratilishida qo'shilishi mumkin (alohida
     * "add columns" migratsiyasi endi kerak emas).
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
            // Erkin matnli kafedra nomi — akademik bo'lmagan xodimlar
            // (masalan bosh mutaxassis, kotib) uchun, ular haqiqiy
            // Department yozuviga bog'lanmasligi mumkin.
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
            // "Professor & o'qituvchilar" ommaviy sahifasidagi Fakultet/
            // Kafedra filtri uchun — faqat type=teacher yozuvlarda
            // to'ldiriladi, boshqalarida null qoladi.
            $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('degree', ['professor', 'dotsent', 'phd', 'oqituvchi'])->nullable();
            // Tadqiqot yo'nalishlari — qisqa teglar ro'yxati (masalan
            // "Sun'iy intellekt", "Mashinali o'rganish").
            $table->json('research_tags')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
