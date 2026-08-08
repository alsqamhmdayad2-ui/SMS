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
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 90.00, 'percentage_to' => 100.00, 'letter_grade' => 'ممتاز', 'gpa_point' => 4.00, 'is_passing' => true, 'minimum_required_percentage' => 50.00],
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 80.00, 'percentage_to' => 89.99, 'letter_grade' => 'جيد جداً', 'gpa_point' => 3.00, 'is_passing' => true, 'minimum_required_percentage' => 50.00],
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 70.00, 'percentage_to' => 79.99, 'letter_grade' => 'جيد', 'gpa_point' => 2.50, 'is_passing' => true, 'minimum_required_percentage' => 50.00],
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 60.00, 'percentage_to' => 69.99, 'letter_grade' => 'متوسط', 'gpa_point' => 2.00, 'is_passing' => true, 'minimum_required_percentage' => 50.00],
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 50.00, 'percentage_to' => 59.99, 'letter_grade' => 'مقبول', 'gpa_point' => 1.00, 'is_passing' => true, 'minimum_required_percentage' => 50.00],
            ['name' => 'سلم الدرجات والتقديرات', 'percentage_from' => 0.00, 'percentage_to' => 49.99, 'letter_grade' => 'راسب', 'gpa_point' => 0.00, 'is_passing' => false, 'minimum_required_percentage' => 50.00],
        ];

        foreach ($scales as $scale) {
            \App\Models\GradeScale::create($scale);
        }
    }
}
