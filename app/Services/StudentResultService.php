<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\StudentSubjectGrade;
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
    public function getStudentResult(Student $student, $academicYearId, $semesterId = null): array
    {
        // Get all subjects the student has results in
        $subjectIds = Exam::where('academic_year_id', $academicYearId)
            ->whereHas('sections', function ($q) use ($student) {
                $q->where('sections.id', $student->section_id);
            })
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->whereHas('questions') // Only exams with questions
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $subjectResults = [];
        $totalGpa = 0;
        $gpaCount = 0;
        $passedCount = 0;
        $failedCount = 0;

        $components = collect(\App\Constants\AssessmentComponents::getAll())->map(fn($item) => (object)$item);

        foreach ($subjects as $subject) {
            // Get exams for this subject/section
            $exams = Exam::where('academic_year_id', $academicYearId)
                ->where('subject_id', $subject->id)
                ->whereHas('sections', function ($q) use ($student) {
                    $q->where('sections.id', $student->section_id);
                })
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->get();

            $examIds = $exams->pluck('id');
            $results = ExamResult::where('student_id', $student->id)
                ->whereIn('exam_id', $examIds)
                ->get();

            // Build component scores
            $componentScores = [];
            $globalObtained = 0.0;
            $globalMax = 0.0;

            foreach ($components as $comp) {
                $compExams = $exams->filter(fn($e) => strtoupper($e->type) === strtoupper($comp->code));
                $obtained = 0;
                $total = 0;

                foreach ($compExams as $exam) {
                    $result = $results->firstWhere('exam_id', $exam->id);
                    if ($result && $result->attendance_status === 'present' && $result->marks_obtained !== null) {
                        $obtained += (float)$result->marks_obtained;
                        $total += (float)$result->total_marks;
                    } elseif ($result) {
                        $total += (float)$result->total_marks;
                    }
                }

                $pct = $total > 0 ? round(($obtained / $total) * 100, 2) : null;
                
                if ($total > 0) {
                    $globalObtained += $obtained;
                    $globalMax += $total;
                }

                $componentScores[] = [
                    'name' => $comp->name,
                    'code' => $comp->code,
                    'obtained' => $obtained,
                    'total' => $total,
                    'percentage' => $pct,
                    'weight' => 0.0,
                    'contribution' => $pct,
                ];
            }

            $overallPercentage = $globalMax > 0 ? round(($globalObtained / $globalMax) * 100, 2) : 0.0;

            // Resolve grade
            $gradeInfo = $this->resolveGrade($overallPercentage);

            // Get saved grade for rank
            $savedGrade = StudentSubjectGrade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $academicYearId)
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->first();

            $isPassing = $gradeInfo['is_passing'] ?? null;
            $gpa = $gradeInfo['gpa_point'] ?? null;

            if ($gpa !== null) {
                $totalGpa += $gpa;
                $gpaCount++;
            }
            if ($isPassing === true) $passedCount++;
            if ($isPassing === false) $failedCount++;

            $isPublished = $this->publicationService->canViewResult(
                $student, 
                $academicYearId, 
                $semesterId, 
                $subject->id
            );

            $subjectResults[] = [
                'subject' => $subject,
                'components' => $componentScores,
                'total_percentage' => $overallPercentage,
                'letter_grade' => $gradeInfo['letter_grade'] ?? null,
                'gpa_points' => $gpa,
                'is_passing' => $isPassing,
                'rank' => $savedGrade->rank_in_section ?? null,
                'is_finalized' => $savedGrade->is_finalized ?? false,
                'is_published' => $isPublished,
            ];
        }

        $overallGpa = $gpaCount > 0 ? round($totalGpa / $gpaCount, 2) : null;
        $avgPercentage = count($subjectResults) > 0
            ? round(collect($subjectResults)->avg('total_percentage'), 2)
            : 0;

        // Determine overall status
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

    protected function resolveGrade($percentage): array
    {
        if ($percentage === null || $percentage == 0) return [];
        
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
}
