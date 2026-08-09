<?php

namespace App\Services;

use App\Models\StudentSubjectGrade;
use App\Models\ExamResult;
use Carbon\Carbon;

class GradeCalculationService
{
    /**
     * Determine the Letter Grade and GPA points for a given percentage using the hardcoded scale.
     */
    public function calculateGradeScale(float $percentage): array
    {
        if ($percentage >= 90) {
            return ['letter_grade' => 'ممتاز', 'gpa_point' => 4.00, 'is_passing' => true];
        } elseif ($percentage >= 80) {
            return ['letter_grade' => 'جيد جداً', 'gpa_point' => 3.00, 'is_passing' => true];
        } elseif ($percentage >= 70) {
            return ['letter_grade' => 'جيد', 'gpa_point' => 2.50, 'is_passing' => true];
        } elseif ($percentage >= 60) {
            return ['letter_grade' => 'متوسط', 'gpa_point' => 2.00, 'is_passing' => true];
        } elseif ($percentage >= 50) {
            return ['letter_grade' => 'مقبول', 'gpa_point' => 1.00, 'is_passing' => true];
        } else {
            return ['letter_grade' => 'راسب', 'gpa_point' => 0.00, 'is_passing' => false];
        }
    }

    /**
     * Calculate and snapshot the student's final grade for a subject.
     * This aggregates all assessment components and their results.
     */
    public function calculateSubjectGrade($studentId, $subjectId, $academicYearId, $semesterId, $sectionId)
    {
        $totalMarksObtained = 0.0;
        $totalMaxMarks = 0.0;

        $examResults = ExamResult::whereHas('exam', function ($q) use ($subjectId, $academicYearId, $semesterId) {
            $q->where('subject_id', $subjectId)
              ->where('academic_year_id', $academicYearId)
              ->where('semester_id', $semesterId)
              ->whereIn('type', [
                  \App\Constants\AssessmentComponents::ACTIVITY,
                  \App\Constants\AssessmentComponents::ATTENDANCE,
                  \App\Constants\AssessmentComponents::ASSIGNMENTS,
                  \App\Constants\AssessmentComponents::MONTHLY_1,
                  \App\Constants\AssessmentComponents::MIDTERM,
                  \App\Constants\AssessmentComponents::MONTHLY_2,
                  \App\Constants\AssessmentComponents::FINAL,
              ]);
        })
        ->where('student_id', $studentId)
        ->whereNotNull('marks_obtained')
        ->get();

        foreach ($examResults as $result) {
            $totalMarksObtained += (float)$result->marks_obtained;
            $totalMaxMarks += (float)$result->total_marks;
        }

        $totalPercentage = $totalMaxMarks > 0 ? ($totalMarksObtained / $totalMaxMarks) * 100 : 0.0;

        // Now map $totalPercentage to GradeScale
        $gradeScale = $this->calculateGradeScale($totalPercentage);

        return StudentSubjectGrade::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ],
            [
                'section_id' => $sectionId,
                'total_percentage' => $totalPercentage,
                'letter_grade' => $gradeScale['letter_grade'],
                'gpa_points' => $gradeScale['gpa_point'],
                'is_passing' => $gradeScale['is_passing'],
                'calculated_at' => Carbon::now(),
            ]
        );
    }
}
