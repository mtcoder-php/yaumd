<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DirectionSubject>
 */
class DirectionSubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Direction'ning o'zi uchun Factory yo'q (fakultet/yo'nalishlar
            // FacultyDirectionSeeder orqali haqiqiy HEMIS ma'lumotlari bilan
            // oldindan yaratiladi) — shu sababli bu yerda faqat MAVJUD
            // yo'nalishdan tasodifiy tanlanadi.
            'direction_id'       => Direction::inRandomOrder()->value('id'),
            'subject_id'         => Subject::inRandomOrder()->value('id') ?? Subject::factory(),
            'block_type'         => fake()->randomElement(['mandatory', 'specialty_1', 'specialty_2']),
            'questions_count'    => fake()->randomElement([10, 15, 20]),
            'score_per_question' => fake()->randomElement([1.0, 1.1, 1.5, 2.0]),
            'is_active'          => true,
        ];
    }
}
