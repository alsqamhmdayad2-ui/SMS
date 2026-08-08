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
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'أنشطة وواجبات', 'code' => 'ACTIVITIES', 'weight_percentage' => 10.00, 'order' => 1],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'حضور ومشاركة', 'code' => 'ATTENDANCE', 'weight_percentage' => 10.00, 'order' => 2],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'اختبار قصير', 'code' => 'QUIZ', 'weight_percentage' => 10.00, 'order' => 3],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'امتحان نصف الفصل', 'code' => 'MIDTERM', 'weight_percentage' => 30.00, 'order' => 4],
            ['academic_year_id' => $academicYear->id, 'subject_id' => $subject->id, 'name' => 'امتحان نهاية الفصل', 'code' => 'FINAL', 'weight_percentage' => 40.00, 'order' => 5],
        ];

        foreach ($components as $component) {
            \App\Models\AssessmentComponent::create($component);
        }
    }
}
