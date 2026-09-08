<?php

namespace Database\Factories;

use App\Models\TestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Test>
 */
class TestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id'      => TestCategory::inRandomOrder()->value('id') ?? TestCategory::factory(),
            'name'             => 'Kirish testi — '.fake()->words(2, true),
            'duration_minutes' => fake()->randomElement([60, 90, 120]),
            'total_questions'  => fake()->randomElement([30, 45, 60]),
            'pass_score'       => fake()->numberBetween(50, 75),
            'is_active'        => true,
        ];
    }
}
