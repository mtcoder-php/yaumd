<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use Database\Factories\CourseCategoryFactory;
use Database\Factories\CourseFactory;
use Illuminate\Database\Seeder;

/**
 * "Dasturni testlash" uchun soxta ma'lumotlar — 3-bosqich: masofaviy ta'lim
 * (LMS) qismi — kurs kategoriyalari, kurslar, modullar, darslar va
 * talabalarning kurslarga yozilishi (Enrollment).
 *
 * MUHIM: AdmissionSeeder (talabalar, User hisoblari) va AcademicStructureSeeder
 * (xodimlar/demo foydalanuvchilar) BU SEEDERDAN OLDIN ishga tushirilgan
 * bo'lishi shart — DatabaseSeeder shu tartibni saqlaydi.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCourseCategories();
        $courses = $this->seedCourses($categories);
        $this->seedModulesAndLessons($courses);
        $this->seedEnrollments($courses);

        $this->command->info('✓ Kurs kategoriyalari, kurslar, modullar, darslar va kursga yozilishlar (enrollments) yaratildi!');
    }

    /**
     * CourseCategoryFactory::NAMES ro'yxatidagi BARCHA 6 ta kategoriya —
     * takrorlanmasin uchun firstOrCreate (name_uz bo'yicha).
     */
    private function seedCourseCategories()
    {
        return collect(CourseCategoryFactory::NAMES)->map(
            fn (array $item) => CourseCategory::firstOrCreate(
                ['name_uz' => $item['uz']],
                [
                    'name_ru'   => $item['uz'],
                    'name_en'   => $item['uz'],
                    'icon'      => $item['icon'],
                    'color'     => $item['color'],
                    'order'     => 0,
                    'is_active' => true,
                ]
            )
        );
    }

    /**
     * CourseFactory::TITLES ichida bor-yo'g'i 10 ta noyob (real) kurs nomi
     * bor (fake()->unique() shu ro'yxat bilan cheklangan) — shuning uchun
     * aynan shu 10 tasi yaratiladi, kategoriyalar orasida navbat bilan
     * taqsimlanadi. Har biriga haqiqiy o'qituvchi/admin `created_by`
     * sifatida biriktiriladi (Factory'dagi tasodifiy foydalanuvchi o'rniga).
     */
    private function seedCourses($categories)
    {
        if (Course::count() > 0) {
            $this->command->warn('CourseSeeder: Kurslar allaqachon mavjud, kurslarni yaratish o\'tkazib yuborildi.');

            return Course::all();
        }

        $creatorId = User::role('teacher')->inRandomOrder()->value('id')
            ?? User::role('admin')->inRandomOrder()->value('id')
            ?? User::inRandomOrder()->value('id');

        return collect(range(0, count(CourseFactory::TITLES) - 1))->map(
            fn (int $i) => Course::factory()->create([
                'category_id' => $categories[$i % $categories->count()]->id,
                'created_by'  => $creatorId,
            ])
        );
    }

    /**
     * Har bir kursga 2-4 ta modul, har bir modulga 3-6 ta dars.
     */
    private function seedModulesAndLessons($courses): void
    {
        $courses->each(function (Course $course) {
            if ($course->modules()->count() > 0) {
                return; // qayta ishga tushirilganda takrorlanmasin
            }

            $moduleCount = fake()->numberBetween(2, 4);

            for ($m = 0; $m < $moduleCount; $m++) {
                $module = CourseModule::factory()->create([
                    'course_id' => $course->id,
                    'order'     => $m,
                ]);

                $lessonCount = fake()->numberBetween(3, 6);

                for ($l = 0; $l < $lessonCount; $l++) {
                    Lesson::factory()->create([
                        'module_id' => $module->id,
                        'order'     => $l,
                        'is_free'   => $m === 0 && $l === 0, // har bir kursning 1-darsi bepul (sinov uchun)
                    ]);
                }
            }
        });
    }

    /**
     * Nashr etilgan (published) kurslarga talabalarni yozadi —
     * (course_id, user_id) unique cheklovi buzilmasin uchun har bir
     * talaba-kurs juftligi faqat bir marta ishlatiladi.
     */
    private function seedEnrollments($courses): void
    {
        if (Enrollment::count() > 0) {
            $this->command->warn('CourseSeeder: Enrollment yozuvlari allaqachon mavjud, o\'tkazib yuborildi.');

            return;
        }

        $publishedCourses = $courses->where('status', 'published')->values();

        if ($publishedCourses->isEmpty()) {
            return;
        }

        $studentUserIds = Student::whereNotNull('user_id')->pluck('user_id')->shuffle();

        $studentUserIds->each(function (int $userId) use ($publishedCourses) {
            // Har bir talaba 1-3 ta tasodifiy (takrorlanmas) kursga yoziladi.
            $picks = $publishedCourses->random(min(fake()->numberBetween(1, 3), $publishedCourses->count()));

            foreach ($picks as $course) {
                $isPaid = $course->type === 'paid';
                $progress = fake()->randomElement([0, 10, 25, 40, 55, 70, 85, 100]);

                Enrollment::firstOrCreate(
                    ['course_id' => $course->id, 'user_id' => $userId],
                    [
                        'payment_type'   => $isPaid ? fake()->randomElement(['click', 'payme']) : 'free',
                        'payment_status' => 'paid',
                        'amount'         => $isPaid ? $course->purchasePrice() : 0,
                        'transaction_id' => $isPaid ? fake()->uuid() : null,
                        'receipt'        => null,
                        'progress'       => $progress,
                        'status'         => $progress >= 100 ? 'completed' : 'active',
                        'enrolled_at'    => now()->subDays(fake()->numberBetween(1, 180)),
                        'completed_at'   => $progress >= 100 ? now()->subDays(fake()->numberBetween(0, 30)) : null,
                        'expires_at'     => null,
                    ]
                );
            }
        });
    }
}
