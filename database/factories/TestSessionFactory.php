<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\TestSession>
 */
class TestSessionFactory extends Factory
{
    public function definition(): array
    {
        $totalQuestions = fake()->randomElement([30, 45]);
        $correct = fake()->numberBetween(0, $totalQuestions);
        $passwordPlain = Str::upper(Str::random(8));

        return [
            'applicant_id'     => Applicant::factory(),
            'direction_id'     => Direction::inRandomOrder()->value('id'),
            'language'         => fake()->randomElement(['uz', 'ru']),
            'foreign_lang'     => fake()->randomElement(['en', 'ar']),
            'login'            => 'T'.fake()->unique()->numerify('######'),
            'password_plain'   => $passwordPlain,
            'password'         => Hash::make($passwordPlain),
            'score'            => null,
            'correct_answers'  => null,
            'total_questions'  => $totalQuestions,
            'status'           => 'pending',
            'started_at'       => null,
            'finished_at'      => null,
            'expires_at'       => now()->addDays(3),
            'answers'          => null,
            'questions'        => null,
        ];
    }

    /**
     * Test yakunlangan — natija (ball, to'g'ri javoblar) allaqachon mavjud.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $total = $attributes['total_questions'] ?? 30;
            $correct = fake()->numberBetween(0, $total);

            return [
                'status'          => 'completed',
                'correct_answers' => $correct,
                'score'           => round($correct * fake()->randomFloat(1, 1.0, 2.0), 1),
                'started_at'      => now()->subDays(fake()->numberBetween(1, 20))->subMinutes(90),
                'finished_at'     => now()->subDays(fake()->numberBetween(1, 20)),
            ];
        });
    }
}
