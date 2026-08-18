<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name'         => $this->faker->firstName(),
            'father_name'        => $this->faker->firstName('male'),
            'grandfather_name'   => $this->faker->firstName('male'),
            'family_name'        => $this->faker->lastName(),
            'national_id'        => $this->faker->unique()->numerify('##########'),
            'phone'              => $this->faker->phoneNumber(),
            'specialization'     => $this->faker->jobTitle(),
            'salary'             => $this->faker->randomFloat(2, 2000, 5000),
            'max_weekly_periods' => $this->faker->numberBetween(15, 24),
            'address'            => $this->faker->address(),
        ];
    }
}
