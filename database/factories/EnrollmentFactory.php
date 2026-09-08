<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        $progress = fake()->randomElement([0, 10, 25, 40, 55, 70, 85, 100]);

        return [
            'course_id'      => Course::factory(),
            'user_id'        => User::inRandomOrder()->value('id') ?? User::factory(),
            'payment_type'   => 'free',
            'payment_status' => 'paid',
            'amount'         => 0,
            'transaction_id' => null,
            'receipt'        => null,
            'progress'       => $progress,
            'status'         => $progress >= 100 ? 'completed' : 'active',
            'enrolled_at'    => now()->subDays(fake()->numberBetween(1, 180)),
            'completed_at'   => $progress >= 100 ? now()->subDays(fake()->numberBetween(0, 30)) : null,
            'expires_at'     => null,
        ];
    }

    public function paidVia(string $provider, float $amount): static
    {
        return $this->state(fn () => [
            'payment_type'   => $provider,
            'payment_status' => 'paid',
            'amount'         => $amount,
        ]);
    }
}
