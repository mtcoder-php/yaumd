<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        // Sentyabrdan sentyabrgacha bo'lgan o'quv yili — real universitet
        // kalendariga mos (masalan "2025-2026", 2025-09-01 dan
        // 2026-06-30 gacha).
        $startYear = fake()->numberBetween(2020, now()->year);

        return [
            'name'       => "{$startYear}-".($startYear + 1),
            'start_date' => "{$startYear}-09-01",
            'end_date'   => ($startYear + 1).'-06-30',
            'is_active'  => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }
}
