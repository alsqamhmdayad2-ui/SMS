<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamResultFactory extends Factory
{
    public function definition(): array
    {
        $marks = $this->faker->numberBetween(0, 100);
        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'marks_obtained' => $marks,
            'total_marks' => 100,
            'percentage' => $marks,
            'attendance_status' => 'present',
            'attempt_number' => 1,
            'remarks' => $this->faker->sentence(),
            'submitted_at' => now(),
            'graded_at' => now(),
            'graded_by' => User::factory(),
        ];
    }
}
