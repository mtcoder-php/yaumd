<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaffExperience>
 */
class StaffExperienceFactory extends Factory
{
    private const PLACES = [
        'IT Park', 'SoftTech Solutions', 'Yangi Asr Universiteti',
        'Milliy TV', 'UzInfoCom', 'Ilm-Ziyo markazi',
    ];

    private const ROLES = [
        'Yetakchi dasturchi', 'Dasturiy ta\'minot muhandisi', 'Katta o\'qituvchi',
        'Dotsent', 'Kafedra mudiri', 'Ilmiy xodim',
    ];

    public function definition(): array
    {
        $startYear = fake()->numberBetween(2010, 2022);
        $endYear = fake()->numberBetween($startYear + 1, 2025);

        return [
            'period'     => "{$startYear} – {$endYear}",
            'title'      => fake()->randomElement(self::PLACES),
            'subtitle'   => fake()->randomElement(self::ROLES),
            'sort_order' => 0,
        ];
    }

    /**
     * Eng so'nggi (davom etayotgan) ish tajribasi yozuvi uchun.
     */
    public function current(): static
    {
        return $this->state(fn () => [
            'period' => fake()->numberBetween(2020, 2023).' – hozir',
        ]);
    }
}
