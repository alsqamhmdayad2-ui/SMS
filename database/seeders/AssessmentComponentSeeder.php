<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assuming we apply this to the first subject and first academic year available
        $academicYear = \App\Models\AcademicYear::first();
        $subject = \App\Models\Subject::first();

        if (!$academicYear || !$subject) {
            return; // Can't seed without related models
        }

        $components = [
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Homework', 'code' => 'HOMEWORK', 'weight_percentage' => 10.00, 'order' => 1],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Attendance', 'code' => 'ATTENDANCE', 'weight_percentage' => 10.00, 'order' => 2],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Quiz', 'code' => 'QUIZ', 'weight_percentage' => 20.00, 'order' => 3],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Monthly Exam', 'code' => 'MONTHLY', 'weight_percentage' => 20.00, 'order' => 4],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Midterm', 'code' => 'MIDTERM', 'weight_percentage' => 20.00, 'order' => 5],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'Final Exam', 'code' => 'FINAL', 'weight_percentage' => 20.00, 'order' => 6],
        ];

        foreach ($components as $component) {
            \App\Models\AssessmentComponent::create($component);
        }
    }
}
