<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\StudentGroup>
 */
class StudentGroupFactory extends Factory
{
    public function definition(): array
    {
        $courseYear = fake()->numberBetween(1, 4);

        return [
            'academic_year_id' => AcademicYear::inRandomOrder()->value('id'),
            'direction_id'     => Direction::inRandomOrder()->value('id'),
            'department_id'    => null,
            'tutor_id'         => null,
            'hemis_id'         => fake()->unique()->numerify('##########'),
            // Masalan "MT-1-25" — yo'nalish qisqartmasi, kurs, qabul yili.
            'name'             => Str::upper(fake()->lexify('??')).'-'.$courseYear.'-'.fake()->unique()->numerify('##'),
            'degree'           => 'bachelor',
            'study_form'       => fake()->randomElement(['full_time', 'full_time', 'evening']),
            'course_year'      => $courseYear,
            'is_active'        => true,
        ];
    }
}
