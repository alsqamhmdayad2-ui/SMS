<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolEvent>
 */
class SchoolEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'event_type' => fake()->randomElement(['holiday', 'event', 'meeting', 'activity', 'other']),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'start_time' => null,
            'end_time' => null,
            'is_all_day' => true,
            'status' => true,
        ];
    }
}
