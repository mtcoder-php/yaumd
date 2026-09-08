<?php

namespace Database\Factories;

use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    // CourseSeeder shu ro'yxatning to'liq uzunligini bilishi kerak
    // (nechta kurs yaratish mumkinligini aniqlash uchun), shuning uchun
    // SubjectFactory::NAMES / CourseCategoryFactory::NAMES kabi public.
    public const TITLES = [
        "Zamonaviy pedagogik texnologiyalar",
        "Ingliz tili: boshlang'ich daraja",
        "Veb-dasturlashga kirish",
        "Bolalar psixologiyasi asoslari",
        "Maxsus pedagogika: amaliy usullar",
        "Office dasturlarida ish yuritish",
        "Loyiha asosida ta'lim (PBL)",
        "Ilmiy maqola yozish asoslari",
        "Raqamli ta'lim vositalari",
        "Nutq va notiqlik san'ati",
    ];

    public function definition(): array
    {
        $title = fake()->unique()->randomElement(self::TITLES);
        $type = fake()->randomElement(['open', 'free', 'paid', 'paid', 'students_only']);
        $price = $type === 'paid' ? fake()->randomElement([50000, 100000, 150000, 200000, 300000]) : 0;

        return [
            'category_id'      => CourseCategory::inRandomOrder()->value('id') ?? CourseCategory::factory(),
            'created_by'       => User::inRandomOrder()->value('id') ?? User::factory(),
            'title_uz'         => $title,
            'title_ru'         => $title,
            'title_en'         => $title,
            'description_uz'   => fake()->realText(220),
            'description_ru'   => null,
            'description_en'   => null,
            'what_you_learn'   => [fake()->sentence(6), fake()->sentence(6), fake()->sentence(6)],
            'requirements'     => [fake()->sentence(5)],
            'thumbnail'        => null,
            'promo_video'      => null,
            'type'             => $type,
            'scorm_type'       => 'native',
            'level'            => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'language'         => 'uz',
            'degree'           => fake()->randomElement(['bachelor', 'master', 'both']),
            'price'            => $price,
            'discount_price'   => $price > 0 && fake()->boolean(30) ? round($price * 0.8) : null,
            'duration_hours'   => fake()->numberBetween(4, 40),
            'has_certificate'  => true,
            'is_sequential'    => true,
            'rating_avg'       => fake()->randomFloat(2, 3.5, 5.0),
            'rating_count'     => fake()->numberBetween(0, 120),
            'students_count'   => 0,
            'status'           => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function free(): static
    {
        return $this->state(fn () => ['type' => 'free', 'price' => 0, 'discount_price' => null]);
    }
}
