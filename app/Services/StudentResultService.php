<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\StudentSubjectGrade;
use App\Models\StudentSemesterMark;
use Illuminate\Support\Collection;

class StudentResultService
{
    public function __construct(
        protected GradebookService $gradebookService,
        protected ResultPublicationService $publicationService
    ) {}

    /**
     * Get full result for a single student across all subjects for a given year/semester.
     */
    public function getStudentResult(Student $student, $academicYearId, $semesterId = null, $ignorePublication = false): array
    {
        // Check if results are published for this student's section
        // If not published, we return empty so they can't see drafts, unless overridden
        $isPublished = $this->publicationService->canViewResult(
            $student, 
            $academicYearId, 
            $semesterId
        );

        if (!$isPublished && !$ignorePublication) {
            return [
                'student' => $student,
                'subjects' => [],
                'summary' => [
                    'overall_gpa' => null,
                    'average_percentage' => 0,
                    'total_subjects' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'status' => 'pending',
                ],
            ];
        }

        $marks = StudentSemesterMark::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        $subjectResults = [];
        $totalGpa = 0;
        $gpaCount = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($marks as $mark) {
            $total = (float)$mark->total;
            $isPassing = $mark->isPassing();
            $gpa = $this->resolveGpa($total);

            if ($gpa !== null) {
                $totalGpa += $gpa;
                $gpaCount++;
            }
            if ($isPassing === true) $passedCount++;
            if ($isPassing === false) $failedCount++;

            $components = [
                ['name' => 'النشاط', 'obtained' => $mark->activity, 'total' => 10, 'details' => []],
                ['name' => 'المواظبة (الحضور)', 'obtained' => $mark->attendance, 'total' => 10, 'details' => []],
                ['name' => 'الواجبات', 'obtained' => $mark->homework, 'total' => 10, 'details' => []],
                ['name' => 'الاختبار الشهري 1', 'obtained' => $mark->monthly1, 'total' => 10, 'details' => []],
                ['name' => 'اختبار منتصف الفصل', 'obtained' => $mark->midterm, 'total' => 20, 'details' => []],
                ['name' => 'الاختبار الشهري 2', 'obtained' => $mark->monthly2, 'total' => 10, 'details' => []],
                ['name' => 'الاختبار النهائي', 'obtained' => $mark->final, 'total' => 30, 'details' => []],
            ];

            $subjectResults[] = [
                'subject' => $mark->subject,
                'components' => $components,
                'total_percentage' => $total,
                'letter_grade' => $mark->letter_grade,
                'gpa_points' => $gpa,
                'is_passing' => $isPassing,
                'is_published' => true,
                'is_finalized' => true,
            ];
        }

        $overallGpa = $gpaCount > 0 ? round($totalGpa / $gpaCount, 2) : null;
        $avgPercentage = count($subjectResults) > 0
            ? round(collect($subjectResults)->avg('total_percentage'), 2)
            : 0;

        $overallStatus = 'pending';
        if ($failedCount > 0) $overallStatus = 'failed';
        elseif ($passedCount > 0 && $passedCount == count($subjectResults)) $overallStatus = 'passed';
        elseif ($passedCount > 0) $overallStatus = 'incomplete';

        return [
            'student' => $student,
            'subjects' => $subjectResults,
            'summary' => [
                'overall_gpa' => $overallGpa,
                'average_percentage' => $avgPercentage,
                'total_subjects' => count($subjectResults),
                'passed' => $passedCount,
                'failed' => $failedCount,
                'status' => $overallStatus,
            ],
        ];
    }

    protected function resolveGpa($percentage)
    {
        if ($percentage === null || $percentage == 0) return null;
        if ($percentage >= 90) return 4.00;
        if ($percentage >= 80) return 3.00;
        if ($percentage >= 70) return 2.50;
        if ($percentage >= 60) return 2.00;
        if ($percentage >= 50) return 1.00;
        return 0.00;
    }


}
