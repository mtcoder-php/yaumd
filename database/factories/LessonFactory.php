<?php

namespace Database\Factories;

use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['video', 'video', 'text', 'pdf', 'quiz']);

        return [
            'module_id'        => CourseModule::factory(),
            'title_uz'         => fake()->sentence(4),
            'title_ru'         => null,
            'description'      => fake()->sentence(12),
            'type'             => $type,
            'order'            => fake()->numberBetween(0, 15),
            'duration'         => $type === 'video' ? fake()->numberBetween(4, 25) : 0,
            'is_free'          => fake()->boolean(20),
            'is_published'     => true,
            'content'          => $type === 'text' ? fake()->realText(400) : null,
            'scorm_package_id' => null,
        ];
    }
}
