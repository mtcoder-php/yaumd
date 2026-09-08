<?php

namespace Database\Factories;

use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TestCategory>
 */
class TestCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'direction_id' => Direction::inRandomOrder()->value('id'),
            'name'         => "Qabul testi — ".fake()->year(),
            'description'  => fake()->sentence(10),
            'is_active'    => true,
        ];
    }
}
