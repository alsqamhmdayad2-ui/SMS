<?php

namespace Database\Factories;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParentModelFactory extends Factory
{
    protected $model = ParentModel::class;

    public function definition(): array
    {
        return [
            'full_name'    => $this->faker->name(),
            'guardian_type'=> $this->faker->randomElement(['Father', 'Mother', 'Guardian']),
            'national_id'  => $this->faker->unique()->numerify('##########'),
            'phone_1'      => $this->faker->phoneNumber(),
            'phone_2'      => $this->faker->optional()->phoneNumber(),
            'occupation'   => $this->faker->jobTitle(),
            'address'      => $this->faker->address(),
        ];
    }
}
