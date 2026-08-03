<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scales = [
            ['name' => 'Default Scale', 'percentage_from' => 97.00, 'percentage_to' => 100.00, 'letter_grade' => 'A+', 'gpa_point' => 4.00, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 93.00, 'percentage_to' => 96.99, 'letter_grade' => 'A', 'gpa_point' => 4.00, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 89.00, 'percentage_to' => 92.99, 'letter_grade' => 'B+', 'gpa_point' => 3.30, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 84.00, 'percentage_to' => 88.99, 'letter_grade' => 'B', 'gpa_point' => 3.00, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 80.00, 'percentage_to' => 83.99, 'letter_grade' => 'C+', 'gpa_point' => 2.30, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 75.00, 'percentage_to' => 79.99, 'letter_grade' => 'C', 'gpa_point' => 2.00, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 60.00, 'percentage_to' => 74.99, 'letter_grade' => 'D', 'gpa_point' => 1.00, 'is_passing' => true, 'minimum_required_percentage' => 60.00],
            ['name' => 'Default Scale', 'percentage_from' => 0.00, 'percentage_to' => 59.99, 'letter_grade' => 'F', 'gpa_point' => 0.00, 'is_passing' => false, 'minimum_required_percentage' => 60.00],
        ];

        foreach ($scales as $scale) {
            \App\Models\GradeScale::create($scale);
        }
    }
}
