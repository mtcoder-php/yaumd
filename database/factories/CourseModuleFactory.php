<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CourseModule>
 */
class CourseModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id'    => Course::factory(),
            'title_uz'     => (fake()->numberBetween(1, 8)).'-mavzu: '.fake()->words(3, true),
            'title_ru'     => null,
            'description'  => fake()->sentence(10),
            'order'        => fake()->numberBetween(0, 10),
            'is_published' => true,
        ];
    }
}
