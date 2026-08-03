<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\AssessmentComponent;
use App\Models\StudentSubjectGrade;
use App\Models\GradeScale;
use App\Models\SchoolSetting;
use App\Services\ResultPublicationService;

class SectionReportService implements ReportInterface
{
    public function __construct(
        protected ResultPublicationService $publicationService
    ) {}

    public function getData(ReportFilterData $filters): array
    {
        $students = Student::with(['section.grade', 'section.schoolClass'])
            ->where('section_id', $filters->sectionId)
            ->orderBy('name')
            ->get();

        $scales = GradeScale::where('status', true)->orderByDesc('percentage_from')->get();
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
                    $gradeInfo = $this->resolveGrade((float)$savedGrade->total_percentage, $scales);
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

    protected function resolveGrade($percentage, $scales): array
    {
        if ($percentage === null || $percentage == 0) return [];
        $scale = $scales->first(fn($s) => $percentage >= (float)$s->percentage_from && $percentage <= (float)$s->percentage_to);
        if (!$scale) return [];
        return [
            'letter_grade' => $scale->letter_grade,
            'gpa_point' => (float)$scale->gpa_point,
            'is_passing' => $scale->is_passing,
        ];
    }
}
