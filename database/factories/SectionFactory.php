<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'الشعبة ' . $this->faker->randomLetter(),
            'class_id' => SchoolClass::factory(),
            'status' => 1,
        ];
    }
}
