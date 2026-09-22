<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaffProject>
 */
class StaffProjectFactory extends Factory
{
    private const TITLES = [
        'Universitet elektron ta\'lim platformasini ishlab chiqish',
        'Talabalar reytingini avtomatlashtirilgan baholash tizimi',
        'Kafedra ilmiy-tadqiqot ma\'lumotlar bazasini yaratish',
        'Masofaviy sinov va nazorat tizimini joriy etish',
        'Universitet tarmoq infratuzilmasini modernizatsiya qilish loyihasi',
        'Yosh tadqiqotchilar uchun grant loyihasi',
    ];

    private const ROLES = [
        'Ilmiy rahbar', 'Loyiha ijrochisi', 'Asosiy ishlab chiquvchi',
        'Konsultant', 'Guruh a\'zosi',
    ];

    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2024);
        $endYear = $startYear + fake()->numberBetween(0, 2);

        return [
            'title'          => fake()->randomElement(self::TITLES),
            'description_uz' => fake()->realText(160),
            'period'         => $startYear === $endYear ? (string) $startYear : "{$startYear} – {$endYear}",
            'role_uz'        => fake()->randomElement(self::ROLES),
            'url'            => null,
            'sort_order'     => 0,
        ];
    }
}
