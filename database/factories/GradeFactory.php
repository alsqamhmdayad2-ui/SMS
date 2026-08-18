<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'المرحلة ' . $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
