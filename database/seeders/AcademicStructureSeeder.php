<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Direction;
use App\Models\DirectionSubject;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * "Dasturni testlash" uchun soxta ma'lumotlar — 1-bosqich: o'quv tuzilmasi
 * (o'quv yillari, fanlar, yo'nalish-fan bog'lanishlari, test savollari,
 * xodimlar) va har bir rol uchun tayyor login/parolli demo foydalanuvchilar.
 * Fakultet/kafedra/yo'nalishlar bu yerda YARATILMAYDI — ular haqiqiy HEMIS
 * ma'lumotlari bilan FacultyDirectionSeeder orqali allaqachon mavjud.
 */
class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $years = $this->seedAcademicYears();
        $subjects = $this->seedSubjects();
        $this->seedDirectionSubjects($subjects);
        $this->seedTestQuestions($subjects);
        $this->seedTestCategories();
        $this->seedStaff();
        $this->seedDemoUsers();

        $this->command->info('✓ O\'quv tuzilmasi (o\'quv yillari, fanlar, testlar, xodimlar) yaratildi! Aktiv o\'quv yili: '.$years->firstWhere('is_active', true)?->name);
    }

    /**
     * 3 ta o'quv yili — tasodifiy emas, ketma-ket va haqiqiy (eng so'nggisi
     * aktiv) bo'lishi kerak, shuning uchun Factory emas, aniq qiymatlar.
     */
    private function seedAcademicYears()
    {
        $currentYear = (int) now()->format('Y');
        $startYears = [$currentYear - 2, $currentYear - 1, $currentYear];

        $years = collect($startYears)->map(function (int $start, int $index) use ($startYears) {
            return AcademicYear::firstOrCreate(
                ['name' => "{$start}-".($start + 1)],
                [
                    'start_date' => "{$start}-09-01",
                    'end_date'   => ($start + 1).'-06-30',
                    'is_active'  => $index === array_key_last($startYears),
                ]
            );
        });

        return $years;
    }

    /**
     * Qabul testlarida ishlatiladigan fanlar — SubjectFactory::NAMES
     * ro'yxatining BARCHASI (tasodifiy tanlanmagan), takrorlanmasin uchun
     * firstOrCreate.
     */
    private function seedSubjects()
    {
        return collect(\Database\Factories\SubjectFactory::NAMES)->map(
            fn (array $pair) => Subject::firstOrCreate(
                ['name_uz' => $pair['uz']],
                ['name_ru' => $pair['ru'], 'is_active' => true]
            )
        );
    }

    /**
     * Har bir (haqiqiy, FacultyDirectionSeeder'dan kelgan) yo'nalishga 1 ta
     * majburiy + 2 ta ixtisoslik fanini biriktiradi — DirectionSubject
     * jadvalidagi (direction_id, subject_id) unique cheklovi tufayli
     * firstOrCreate bilan.
     */
    private function seedDirectionSubjects($subjects): void
    {
        Direction::all()->each(function (Direction $direction) use ($subjects) {
            $picks = $subjects->random(min(3, $subjects->count()))->values();
            $blocks = ['mandatory', 'specialty_1', 'specialty_2'];

            foreach ($picks as $i => $subject) {
                DirectionSubject::firstOrCreate(
                    ['direction_id' => $direction->id, 'subject_id' => $subject->id],
                    [
                        'block_type'         => $blocks[$i] ?? 'specialty_2',
                        'questions_count'    => 10,
                        'score_per_question' => 1.1,
                        'is_active'          => true,
                    ]
                );
            }
        });
    }

    /**
     * Har bir fan uchun ~15 tadan test savoli — TestSession'lar shu
     * savollar orasidan tasodifiy tanlab, JSON sifatida saqlaydi
     * (test_sessions.questions).
     */
    private function seedTestQuestions($subjects): void
    {
        $subjects->each(function (Subject $subject) {
            if ($subject->questions()->count() > 0) {
                return; // qayta ishga tushirilganda takrorlanmasin
            }

            TestQuestion::factory()->count(15)->create(['subject_id' => $subject->id]);
        });
    }

    /**
     * "Test" modeli (Test/TestCategory) hozircha hech qaysi controller
     * tomonidan ishlatilmaydi (qabul testi endi to'g'ridan-to'g'ri
     * DirectionSubject + TestSession orqali ishlaydi) — lekin baza sxemasi
     * hali mavjud bo'lgani uchun, "hammasi uchun Factory/Seeder" talabiga
     * ko'ra shu yerda ham to'ldiriladi.
     */
    private function seedTestCategories(): void
    {
        if (TestCategory::count() > 0) {
            return;
        }

        Direction::all()->each(function (Direction $direction) {
            $category = TestCategory::factory()->create([
                'direction_id' => $direction->id,
                'name'         => "Qabul testi — {$direction->name_uz}",
            ]);

            // Har bir kategoriya uchun 1-2 ta Test yozuvi — "hammasi uchun
            // Factory/Seeder" talabiga ko'ra TestFactory ham ishlatiladi.
            Test::factory()->count(fake()->numberBetween(1, 2))->create([
                'category_id' => $category->id,
            ]);
        });
    }

    private function seedStaff(): void
    {
        if (Staff::count() >= 10) {
            return;
        }

        Staff::factory()->leadership()->count(3)->create();
        Staff::factory()->teacher()->count(20)->create();
        Staff::factory()->count(7)->create(); // oddiy xodimlar
    }

    /**
     * Har bir rol uchun ESLAB QOLISH OSON login/parolli demo hisoblar —
     * Mukhtor har bir dashboard/rolni tezda sinab ko'rishi uchun. Parol
     * hammasida bir xil: "password" (UserFactory'ning standart paroli
     * bilan mos).
     */
    private function seedDemoUsers(): void
    {
        $demo = [
            ['email' => 'admin.demo@yau.uz',     'name' => 'Demo Administrator',    'role' => 'admin'],
            ['email' => 'admission.demo@yau.uz', 'name' => 'Demo Qabul xodimi',     'role' => 'admission'],
            ['email' => 'teacher.demo@yau.uz',   'name' => "Demo O'qituvchi",       'role' => 'teacher'],
            ['email' => 'finance.demo@yau.uz',   'name' => 'Demo Moliya xodimi',    'role' => 'finance'],
            ['email' => 'librarian.demo@yau.uz', 'name' => 'Demo Kutubxonachi',     'role' => 'librarian'],
        ];

        foreach ($demo as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'full_name' => $row['name'],
                    'phone'     => null,
                    'password'  => bcrypt('password'),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$row['role']]);
        }

        $this->command->info('✓ Demo hisoblar (parol: password): admin.demo@yau.uz, admission.demo@yau.uz, teacher.demo@yau.uz, finance.demo@yau.uz, librarian.demo@yau.uz');
    }
}
