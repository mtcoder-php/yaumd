<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaffEducation>
 */
class StaffEducationFactory extends Factory
{
    private const INSTITUTIONS = [
        'Toshkent Axborot Texnologiyalari Universiteti',
        'O\'zbekiston Milliy Universiteti',
        'Toshkent Davlat Iqtisodiyot Universiteti',
        'Samarqand Davlat Universiteti',
        'University of Michigan (AQSh)',
        'Yangi Asr Universiteti',
    ];

    private const DEGREES = [
        'Bakalavriat', 'Magistratura', 'Stajirovka', 'PhD', 'Doktorantura',
    ];

    public function definition(): array
    {
        $startYear = fake()->numberBetween(2000, 2018);
        $endYear = $startYear + fake()->numberBetween(1, 4);

        return [
            'period'     => "{$startYear} – {$endYear}",
            'title'      => fake()->randomElement(self::INSTITUTIONS),
            'subtitle'   => fake()->randomElement(self::DEGREES),
            'sort_order' => 0,
        ];
    }
}
