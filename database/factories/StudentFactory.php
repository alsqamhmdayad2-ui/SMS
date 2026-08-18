<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        static $seq = 0;
        $seq++;
        $year = date('Y');

        return [
            'student_number'   => sprintf('STU-%s-%04d', $year, $seq),
            'first_name'       => $this->faker->firstName(),
            'father_name'      => $this->faker->firstName('male'),
            'grandfather_name' => $this->faker->firstName('male'),
            'family_name'      => $this->faker->lastName(),
            'national_id'      => $this->faker->unique()->numerify('##########'),
            'gender'           => $this->faker->randomElement(['Male', 'Female']),
            'birth_date'       => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'nationality'      => 'فلسطيني',
            'religion'         => 'Muslim',
            'governorate'      => 'رام الله',
            'city'             => 'رام الله',
            'status'           => 'active',
        ];
    }

    public function withEnrollment(): static
    {
        return $this->afterCreating(function ($student) {
            // no-op; enrollment handled separately in tests
        });
    }
}
