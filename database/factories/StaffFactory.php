<?php

namespace Database\Factories;

use Database\Factories\Support\UzbekName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    private const POSITIONS_TEACHER = ["Katta o'qituvchi", 'Dotsent', 'Professor', "O'qituvchi", "Assistent"];
    private const POSITIONS_STAFF   = ['Bosh mutaxassis', 'Mutaxassis', 'Kotib', 'Uslubchi'];
    private const POSITIONS_LEAD    = ['Rektor', 'Prorektor', 'Dekan', 'Kafedra mudiri'];

    public function definition(): array
    {
        $person = UzbekName::person();
        $fullName = "{$person['last_name']} {$person['first_name']} {$person['middle_name']}";
        $type = fake()->randomElement(['teacher', 'teacher', 'teacher', 'staff', 'staff', 'leadership']);

        $position = match ($type) {
            'leadership' => fake()->randomElement(self::POSITIONS_LEAD),
            'teacher'    => fake()->randomElement(self::POSITIONS_TEACHER),
            default      => fake()->randomElement(self::POSITIONS_STAFF),
        };

        return [
            'full_name_uz'     => $fullName,
            'full_name_ru'     => $fullName,
            'full_name_en'     => $fullName,
            'position_uz'      => $position,
            'position_ru'      => $position,
            'position_en'      => $position,
            'department_uz'    => null,
            'department_ru'    => null,
            'department_en'    => null,
            'bio_uz'           => fake()->realText(180),
            'bio_ru'           => null,
            'bio_en'           => null,
            'photo'            => null,
            'email'            => fake()->unique()->safeEmail(),
            'phone'            => '99890'.fake()->unique()->numerify('#######'),
            'reception_hours'  => 'Dushanba-Juma, 09:00-17:00',
            'type'             => $type,
            'order'            => fake()->numberBetween(0, 100),
            'is_active'        => true,
        ];
    }

    public function leadership(): static
    {
        return $this->state(fn () => [
            'type'     => 'leadership',
            'position_uz' => fake()->randomElement(self::POSITIONS_LEAD),
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn () => [
            'type'     => 'teacher',
            'position_uz' => fake()->randomElement(self::POSITIONS_TEACHER),
        ]);
    }
}
