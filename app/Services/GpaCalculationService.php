<?php

namespace App\Services;

use App\Models\StudentSubjectGrade;
use App\Models\Student;
use App\Models\ReportCard;

class GpaCalculationService
{
    /**
     * Calculate GPA and percentages for all students in a section for a specific term,
     * and rank them.
     */
    public function calculateForSection($academicYearId, $semesterId, $sectionId, $reportPeriod = 'semester')
    {
        $students = Student::where('section_id', $sectionId)->get();
        $studentResults = [];

        foreach ($students as $student) {
            // Get all final grades for this student
            $gradesQuery = StudentSubjectGrade::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('is_finalized', true);

            if ($reportPeriod === 'semester' && $semesterId) {
                $gradesQuery->where('semester_id', $semesterId);
            }

            $grades = $gradesQuery->get();

            if ($grades->isEmpty()) {
                continue;
            }

            // Calculate overall percentages and GPAs
            // Currently using simple average (without credits)
            $totalPercentageSum = 0;
            $gpaSum = 0;
            $gpaCount = 0;
            $passedCount = 0;
            $failedCount = 0;

            foreach ($grades as $grade) {
                $totalPercentageSum += (float)$grade->total_percentage;
                
                if ($grade->gpa_points !== null) {
                    $gpaSum += (float)$grade->gpa_points;
                    $gpaCount++;
                }

                if ($grade->is_passing === true) {
                    $passedCount++;
                } elseif ($grade->is_passing === false) {
                    $failedCount++;
                }
            }

            $subjectCount = $grades->count();
            $averagePercentage = $subjectCount > 0 ? round($totalPercentageSum / $subjectCount, 2) : 0;
            $averageGpa = $gpaCount > 0 ? round($gpaSum / $gpaCount, 2) : null;

            $academicStatus = 'Incomplete';
            if ($failedCount > 0) {
                $academicStatus = 'Fail';
            } elseif ($passedCount === $subjectCount && $subjectCount > 0) {
                $academicStatus = 'Pass';
            }

            $studentResults[] = [
                'student' => $student,
                'gpa' => $averageGpa,
                'total_percentage' => $averagePercentage,
                'academic_status' => $academicStatus,
            ];
        }

        // Sort to assign ranks
        usort($studentResults, fn($a, $b) => $b['total_percentage'] <=> $a['total_percentage']);

        $rank = 1;
        $processedData = [];

        foreach ($studentResults as $result) {
            $processedData[$result['student']->id] = [
                'gpa' => $result['gpa'],
                'total_percentage' => $result['total_percentage'],
                'academic_status' => $result['academic_status'],
                'rank_in_section' => $rank,
            ];
            $rank++;
        }

        return $processedData;
    }
}
