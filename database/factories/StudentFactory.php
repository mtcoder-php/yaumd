<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Direction;
use Database\Factories\Support\UzbekName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        $person = UzbekName::person();

        return [
            'applicant_id'     => null,
            'academic_year_id' => AcademicYear::inRandomOrder()->value('id'),
            'direction_id'     => Direction::inRandomOrder()->value('id'),
            'department_id'    => null,
            'hemis_id'         => fake()->unique()->numerify('############'),
            'student_number'   => 'S'.now()->year.fake()->unique()->numerify('#####'),
            'first_name'       => $person['first_name'],
            'last_name'        => $person['last_name'],
            'middle_name'      => $person['middle_name'],
            'passport_series'  => Str::upper(fake()->lexify('??')).fake()->numerify('#######'),
            'jshshir'          => fake()->unique()->numerify('##############'),
            'phone'            => '99890'.fake()->unique()->numerify('#######'),
            'email'            => fake()->unique()->safeEmail(),
            'birth_day'        => fake()->numberBetween(1, 28),
            'birth_month'      => fake()->numberBetween(1, 12),
            'birth_year'       => fake()->numberBetween(now()->year - 28, now()->year - 17),
            'gender'           => $person['gender'],
            'degree'           => 'bachelor',
            'study_form'       => fake()->randomElement(['full_time', 'full_time', 'full_time', 'evening', 'distance']),
            'course_year'      => fake()->numberBetween(1, 4),
            'status'           => 'active',
            'funding_type'     => fake()->randomElement(['contract', 'contract', 'contract', 'grant']),
            'photo'            => null,
            'address'          => fake()->streetAddress(),
            'user_id'          => null,
        ];
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function grant(): static
    {
        return $this->state(fn () => ['funding_type' => 'grant']);
    }
}
