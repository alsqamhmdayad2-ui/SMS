<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['الرياضيات', 'العلوم', 'اللغة العربية', 'التاريخ', 'الجغرافيا', 'الفيزياء', 'الكيمياء', 'الأحياء']),
            'code' => $this->faker->unique()->lexify('SUB-????'),
            'description' => $this->faker->sentence(),
            'status' => 1,
        ];
    }
}
