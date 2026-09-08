<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CourseCategory>
 */
class CourseCategoryFactory extends Factory
{
    // LMS kurslari uchun real kategoriyalar (Faker so'zlaridan ko'ra).
    public const NAMES = [
        ['uz' => 'Dasturlash',              'icon' => 'mdi:code-tags',            'color' => '#3b82f6'],
        ['uz' => 'Tillar',                  'icon' => 'mdi:translate',            'color' => '#22c55e'],
        ['uz' => 'Pedagogika',              'icon' => 'mdi:school-outline',       'color' => '#8b5cf6'],
        ['uz' => 'Raqamli ko\'nikmalar',    'icon' => 'mdi:laptop',               'color' => '#f59e0b'],
        ['uz' => 'Shaxsiy rivojlanish',     'icon' => 'mdi:account-arrow-up',     'color' => '#ec4899'],
        ['uz' => 'Ilmiy tadqiqot asoslari', 'icon' => 'mdi:flask-outline',        'color' => '#14b8a6'],
    ];

    public function definition(): array
    {
        $item = fake()->randomElement(self::NAMES);

        return [
            'parent_id' => null,
            'name_uz'   => $item['uz'],
            'name_ru'   => $item['uz'],
            'name_en'   => $item['uz'],
            'icon'      => $item['icon'],
            'color'     => $item['color'],
            'order'     => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
