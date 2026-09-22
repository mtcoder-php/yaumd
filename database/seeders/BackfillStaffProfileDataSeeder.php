<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Staff;
use App\Models\StaffArticle;
use App\Models\StaffEducation;
use App\Models\StaffExperience;
use App\Models\StaffProject;
use App\Models\StaffSocialLink;
use Illuminate\Database\Seeder;

/**
 * MUHIM: AcademicStructureSeeder'dagi seedStaff() metodi ichida
 * "if (Staff::where('type', '!=', 'leadership')->count() >= 10) return;"
 * degan qo'riqlovchi bor — bazada allaqachon 10 tadan ortiq yozuv bo'lsa,
 * BUTUN metod (20 ta o'qituvchi yaratish + ularga Ta'lim va malaka/Ish
 * tajribasi/Ijtimoiy tarmoqlar/Maqolalar/Loyihalar biriktirish) UMUMAN
 * ishlamay qoladi. Natijada, turli vaqtlarda (bu funksiyalar hali
 * qo'shilmagan paytda) yaratilgan ba'zi o'qituvchi yozuvlari bu
 * ma'lumotlarning HECH BIRIGA ega bo'lmay qolishi mumkin — profil
 * sahifasida "Ta'lim va malaka"/"Ish tajribasi"/"Ilmiy faoliyati"
 * bo'limlari shunchaki ko'rinmay qoladi (v-if bo'sh massivda/qiymatda
 * ishlamaydi). Bu ham KOD emas, MA'LUMOT muammosi.
 *
 * Bu seeder har bir type=teacher yozuvini alohida tekshiradi va FAQAT
 * yetishmayotgan qismlarni to'ldiradi — mavjud yozuvlarni o'chirmaydi
 * yoki qayta yaratmaydi, shuning uchun bir necha marta xavfsiz ishga
 * tushirish mumkin:
 *
 *   php artisan db:seed --class=BackfillStaffProfileDataSeeder
 */
class BackfillStaffProfileDataSeeder extends Seeder
{
    private const RESEARCH_TAGS = [
        'Sun\'iy intellekt', 'Mashinali o\'rganish', 'Web texnologiyalar',
        'Iqtisodiyot nazariyasi', 'Raqamli iqtisodiyot', 'Statistika',
        'Adabiyotshunoslik', 'Til nazariyasi', 'Matn tahlili',
        'Lingvistika', "Xorijiy til ta'limi", 'Kommunikativ yondashuv',
        'Energiya samaradorligi', "Qayta tiklanuvchi energiya", 'Elektr texnikasi',
        'Kardiologiya', 'Profilaktik tibbiyot', "Sog'lom turmush tarzi",
        'Algebra', 'Matematik modellashtirish', 'Ehtimollar nazariyasi',
        "Huquqiy islohotlar", "Fuqarolik huquqi", "Xalqaro huquq",
    ];

    public function run(): void
    {
        $teachers = Staff::where('type', 'teacher')->get();

        foreach ($teachers as $teacher) {
            $this->backfillCoreFields($teacher);
            $this->backfillProfileStats($teacher);

            if ($teacher->educations()->doesntExist()) {
                StaffEducation::factory()
                    ->count(fake()->numberBetween(2, 3))
                    ->sequence(fn ($seq) => ['sort_order' => $seq->index])
                    ->create(['staff_id' => $teacher->id]);
            }

            if ($teacher->experiences()->doesntExist()) {
                StaffExperience::factory()
                    ->count(fake()->numberBetween(2, 3))
                    ->sequence(fn ($seq) => ['sort_order' => $seq->index])
                    ->create(['staff_id' => $teacher->id]);

                $teacher->experiences()->orderBy('sort_order')->first()?->update([
                    'period' => fake()->numberBetween(2020, 2023).' – hozir',
                ]);
            }

            if ($teacher->socialLinks()->doesntExist()) {
                collect(['telegram', 'linkedin', 'google_scholar', 'researchgate'])
                    ->shuffle()
                    ->take(fake()->numberBetween(2, 4))
                    ->values()
                    ->each(function (string $platform, int $index) use ($teacher) {
                        StaffSocialLink::factory()->platform($platform)->create([
                            'staff_id'   => $teacher->id,
                            'sort_order' => $index,
                        ]);
                    });
            }

            if ($teacher->articles()->doesntExist()) {
                StaffArticle::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->sequence(fn ($seq) => ['sort_order' => $seq->index])
                    ->create(['staff_id' => $teacher->id]);
            }

            if ($teacher->projects()->doesntExist()) {
                StaffProject::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->sequence(fn ($seq) => ['sort_order' => $seq->index])
                    ->create(['staff_id' => $teacher->id]);
            }
        }

        $this->command?->info("Backfill tugadi: {$teachers->count()} ta o'qituvchi yozuvi tekshirildi.");
    }

    /**
     * Fakultet/kafedra/tadqiqot yo'nalishlari — "Professor & o'qituvchilar"
     * ro'yxatidagi kartochka uchun.
     */
    private function backfillCoreFields(Staff $teacher): void
    {
        $updates = [];

        if (! $teacher->department_id || ! $teacher->faculty_id) {
            $department = Department::inRandomOrder()->first();
            $updates['department_id'] = $teacher->department_id ?: $department?->id;
            $updates['faculty_id']    = $teacher->faculty_id ?: $department?->faculty_id;
        }

        if (empty($teacher->research_tags)) {
            $updates['research_tags'] = collect(self::RESEARCH_TAGS)
                ->random(fake()->numberBetween(2, 3))
                ->values()
                ->all();
        }

        if ($updates) {
            $teacher->update($updates);
        }
    }

    /**
     * Profil sahifasidagi "Umumiy ma'lumot" tabi — statistika kartalari
     * va "Ilmiy faoliyati" bo'limi uchun.
     */
    private function backfillProfileStats(Staff $teacher): void
    {
        $updates = [];

        if ($teacher->experience_years === null) {
            $updates['experience_years'] = fake()->numberBetween(2, 20);
        }
        if ($teacher->students_count === null) {
            $updates['students_count'] = fake()->numberBetween(30, 400);
        }
        if ($teacher->articles_count === null) {
            $updates['articles_count'] = fake()->numberBetween(3, 60);
        }
        if ($teacher->projects_count === null) {
            $updates['projects_count'] = fake()->numberBetween(0, 15);
        }
        if (! $teacher->location) {
            $updates['location'] = 'Toshkent, O\'zbekiston';
        }
        if (! $teacher->research_summary_uz) {
            $updates['research_summary_uz'] = fake()->realText(220);
        }

        if ($updates) {
            $teacher->update($updates);
        }
    }
}
