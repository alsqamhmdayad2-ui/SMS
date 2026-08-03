<?php

namespace App\Services;

use App\Models\GradeScale;
use App\Models\StudentSubjectGrade;
use App\Models\AssessmentComponent;
use App\Models\ExamResult;
use Carbon\Carbon;

class GradeCalculationService
{
    /**
     * Determine the Letter Grade and GPA points for a given percentage using the Grade Scale.
     */
    public function calculateGradeScale(float $percentage): ?GradeScale
    {
        return GradeScale::where('status', true)
            ->where('percentage_from', '<=', $percentage)
            ->where('percentage_to', '>=', $percentage)
            ->first();
    }

    /**
     * Calculate and snapshot the student's final grade for a subject.
     * This aggregates all assessment components and their results.
     */
    public function calculateSubjectGrade($studentId, $subjectId, $academicYearId, $semesterId, $sectionId)
    {
        // Get all components for this subject
        $components = AssessmentComponent::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $totalPercentage = 0.0;

        foreach ($components as $component) {
            // Find exam result for this component
            // We assume exams are linked to assessment components via the exam's type/code.
            // For now, let's assume we map exam type to component code.
            
            // This is a simplified fetch, actual logic may vary based on how exams map to components
            $examResults = ExamResult::whereHas('exam', function ($q) use ($subjectId, $academicYearId, $semesterId, $component) {
                $q->where('subject_id', $subjectId)
                  ->where('academic_year_id', $academicYearId)
                  ->where('semester_id', $semesterId)
                  ->where('type', strtolower($component->code));
            })
            ->where('student_id', $studentId)
            ->whereNotNull('marks_obtained')
            ->get();

            if ($examResults->isNotEmpty()) {
                // If there are multiple exams of the same type, we might average them or sum them. 
                // Let's assume average percentage for this component code.
                $sumPercentage = 0;
                $validExamsCount = 0;

                foreach ($examResults as $result) {
                    if (!$result->is_absent || $result->is_excused) {
                        $percentage = ($result->marks_obtained / $result->total_marks) * 100;
                        $sumPercentage += $percentage;
                        $validExamsCount++;
                    } else {
                        // Absent without excuse = 0 for this exam
                        $validExamsCount++;
                    }
                }

                if ($validExamsCount > 0) {
                    $componentAvgPercentage = $sumPercentage / $validExamsCount;
                    // Apply component weight
                    $weightedScore = ($componentAvgPercentage * $component->weight_percentage) / 100;
                    $totalPercentage += $weightedScore;
                }
            }
        }

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
                'letter_grade' => $gradeScale ? $gradeScale->letter_grade : null,
                'gpa_points' => $gradeScale ? $gradeScale->gpa_point : null,
                'is_passing' => $gradeScale ? $gradeScale->is_passing : null,
                'calculated_at' => Carbon::now(),
            ]
        );
    }
}
