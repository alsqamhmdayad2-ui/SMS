<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\StudentSubjectGrade;
use App\Models\SchoolSetting;
use App\Services\ResultPublicationService;

class SectionReportService implements ReportInterface
{
    public function __construct(
        protected ResultPublicationService $publicationService
    ) {}

    public function getData(ReportFilterData $filters): array
    {
        $students = Student::with(['section.schoolClass.grade', 'section.schoolClass'])
            ->where('section_id', $filters->sectionId)
            ->orderBy('first_name')
            ->get();

        $schoolSettings = SchoolSetting::first();

        $subjectIds = Exam::where('academic_year_id', $filters->academicYearId)
            ->where('section_id', $filters->sectionId)
            ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $studentsData = [];
        $highestAvg = 0;
        $lowestAvg = 100;
        $totalPassRate = 0;
        $gpaTotals = [];

        foreach ($students as $student) {
            $subjectScores = [];
            $totalGpa = 0;
            $gpaCount = 0;
            $passedCount = 0;
            $failedCount = 0;

            foreach ($subjects as $subject) {
                $isPublished = $this->publicationService->canViewResult(
                    $student, $filters->academicYearId, $filters->semesterId, $subject->id
                );

                if (!$isPublished) {
                    $subjectScores[] = [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'percentage' => null,
                        'letter_grade' => null,
                        'gpa' => null,
                        'status' => 'pending',
                    ];
                    continue;
                }

                $savedGrade = StudentSubjectGrade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $filters->academicYearId)
                    ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
                    ->first();

                if ($savedGrade) {
                    $gradeInfo = $this->resolveGrade((float)$savedGrade->total_percentage);
                    $gpa = $gradeInfo['gpa_point'] ?? null;
                    $isPassing = $gradeInfo['is_passing'] ?? null;

                    if ($gpa !== null) {
                        $totalGpa += $gpa;
                        $gpaCount++;
                    }
                    if ($isPassing === true) $passedCount++;
                    if ($isPassing === false) $failedCount++;

                    $subjectScores[] = [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'percentage' => (float)$savedGrade->total_percentage,
                        'letter_grade' => $gradeInfo['letter_grade'] ?? '-',
                        'gpa' => $gpa,
                        'status' => $isPassing ? 'pass' : 'fail',
                    ];
                } else {
                    $subjectScores[] = [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'percentage' => null,
                        'letter_grade' => '-',
                        'gpa' => null,
                        'status' => 'pending',
                    ];
                }
            }

            $overallGpa = $gpaCount > 0 ? round($totalGpa / $gpaCount, 2) : null;
            $avgPercentage = count($subjectScores) > 0
                ? round(collect($subjectScores)->whereNotNull('percentage')->avg('percentage'), 2) : 0;

            $overallStatus = 'pending';
            if ($failedCount > 0) $overallStatus = 'failed';
            elseif ($passedCount > 0 && $passedCount == count($subjectScores)) $overallStatus = 'passed';
            elseif ($passedCount > 0) $overallStatus = 'incomplete';

            if ($avgPercentage > $highestAvg) $highestAvg = $avgPercentage;
            if ($avgPercentage > 0 && $avgPercentage < $lowestAvg) $lowestAvg = $avgPercentage;

            if ($overallGpa !== null) $gpaTotals[] = $overallGpa;

            $studentsData[] = [
                'student' => $student,
                'subjects' => $subjectScores,
                'average' => $avgPercentage,
                'gpa' => $overallGpa,
                'passed' => $passedCount,
                'failed' => $failedCount,
                'status' => $overallStatus,
            ];
        }

        // Sort by average descending and assign rank
        usort($studentsData, fn($a, $b) => $b['average'] <=> $a['average']);
        $rank = 1;
        foreach ($studentsData as &$sd) {
            $sd['rank'] = $rank++;
        }

        $passRate = count($studentsData) > 0
            ? round(collect($studentsData)->where('status', 'passed')->count() / count($studentsData) * 100, 1)
            : 0;

        $canGenerateOfficial = $this->publicationService->canGenerateOfficialReport(
            $filters->academicYearId, $filters->semesterId, $filters->sectionId
        );

        return [
            'school' => $schoolSettings,
            'subjects' => $subjects,
            'students' => $studentsData,
            'can_generate_official' => $canGenerateOfficial,
            'statistics' => [
                'total_students' => count($studentsData),
                'highest_average' => $highestAvg,
                'lowest_average' => $lowestAvg > 100 ? 0 : $lowestAvg,
                'class_average' => count($gpaTotals) > 0 ? round(array_sum($gpaTotals) / count($gpaTotals), 2) : 0,
                'pass_rate' => $passRate,
            ],
        ];
    }

    public function getViewTemplate(): string
    {
        return 'reports.section';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->sectionId !== null && $filters->academicYearId !== null;
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
