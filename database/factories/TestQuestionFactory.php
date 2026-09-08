<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TestQuestion>
 */
class TestQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id'     => Subject::inRandomOrder()->value('id') ?? Subject::factory(),
            'language'       => fake()->randomElement(['uz', 'ru']),
            'question'       => fake()->sentence(8).'?',
            'option_a'       => fake()->words(3, true),
            'option_b'       => fake()->words(3, true),
            'option_c'       => fake()->words(3, true),
            'option_d'       => fake()->words(3, true),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'order'          => fake()->numberBetween(0, 50),
            'is_active'      => true,
        ];
    }
}
