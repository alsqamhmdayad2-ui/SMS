<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Midterm Exam - ' . $this->faker->word(),
            'type' => $this->faker->randomElement(['midterm', 'final', 'quiz', 'monthly']),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'grade_id' => Grade::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'exam_date' => clone $this->faker->dateTimeBetween('now', '+1 month'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'duration_minutes' => 120,
            'status' => ExamStatus::DRAFT->value,
            'display_mode' => 'online',
            'instructions' => $this->faker->paragraph(),
            'total_marks' => 100,
            'show_marks_to_student' => true,
            'show_answers_to_student' => true,
        ];
    }
}
