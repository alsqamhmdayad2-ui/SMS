<?php

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['الفصل الدراسي الأول', 'الفصل الدراسي الثاني', 'الفصل الصيفي']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => false,
        ];
    }
}
