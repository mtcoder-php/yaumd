<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turniket terminallaridan kelayotgan "employeeNoString" (masalan "s6236"
 * yoki "u596" — eski uchinchi tomon CRM tomonidan tayinlangan, YAUMD'ning
 * o'z ID'lariga umuman bog'liq bo'lmagan raqam) bilan YAUMD'dagi haqiqiy
 * talaba ('students') yoki xodim ('users') yozuvi o'rtasidagi moslikni
 * saqlaydi.
 *
 * Har bir employee_no uchun BITTA yozuv bor (PersonMatchingService/
 * MatchTurnstilePeople buyrug'i shu jadvalni to'ldiradi/yangilaydi):
 *   - 'auto_matched' — tizim ism bo'yicha ANIQ (bir xil nomzod, to'liq
 *     token mosligi) topgan, admin tasdig'i shart emas.
 *   - 'needs_review'  — bir nechta ehtimoliy nomzod bor yoki moslik
 *     to'liq emas — admin panelida ko'rib chiqilishi kerak.
 *   - 'matched'        — admin qo'lda tasdiqlagan (yoki taklif etilgan
 *     nomzodni tanlagan).
 *   - 'unmatched'      — hech qanday mos nomzod topilmadi (masalan ism
 *     talabalar/xodimlar bazasida umuman yo'q).
 *   - 'rejected'       — admin ataylab "bu employeeNo uchun mos YAUMD
 *     yozuvi yo'q" deb belgilagan (masalan mehmon/tashqi shaxs) — bu
 *     holatda MatchTurnstilePeople uni --force bo'lmasa qayta tekshirmaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_matches', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 50)->unique();

            // Terminaldan kelgan oxirgi ko'rilgan ism (moslashtirish
            // algoritmi ishlatgan xom matn — admin panelida ko'rsatish va
            // diagnostika uchun saqlanadi).
            $table->string('person_name', 255)->nullable();

            // Polimorfik bog'lanish — 'student' => App\Models\Student,
            // 'staff' => App\Models\User (AppServiceProvider'dagi
            // Relation::morphMap()ga qarang). Hali hech narsa
            // topilmagan/tasdiqlanmagan bo'lsa — ikkalasi ham null.
            $table->string('matchable_type', 20)->nullable();
            $table->unsignedBigInteger('matchable_id')->nullable();

            $table->enum('status', ['auto_matched', 'needs_review', 'matched', 'unmatched', 'rejected'])
                ->default('needs_review');

            // Eng yaxshi nomzodning moslik darajasi (0..1) — faqat
            // diagnostika/UI uchun, biznes mantiq 'status'ga asoslanadi.
            $table->decimal('confidence', 5, 4)->nullable();

            // Algoritm taklif etgan eng yaxshi (eng ko'pi bilan 5 ta)
            // nomzodlar ro'yxati — admin panelida "shulardan birini
            // tanlang" tugmalarini chizish uchun:
            // [{type:'student', id:12, name:'...', score:0.87}, ...]
            $table->json('candidates')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['matchable_type', 'matchable_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_matches');
    }
};
