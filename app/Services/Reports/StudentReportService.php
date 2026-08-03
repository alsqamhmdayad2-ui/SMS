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
use Illuminate\Support\Str;

class StudentReportService implements ReportInterface
{
    public function __construct(
        protected ResultPublicationService $publicationService
    ) {}

    public function getData(ReportFilterData $filters): array
    {
        $student = Student::with(['grade', 'schoolClass', 'section'])->findOrFail($filters->studentId);
        $scales = GradeScale::where('status', true)->orderByDesc('percentage_from')->get();
        $schoolSettings = SchoolSetting::first();

        $subjectIds = Exam::where('academic_year_id', $filters->academicYearId)
            ->where('section_id', $student->section_id)
            ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $subjectResults = [];
        $totalGpa = 0;
        $gpaCount = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($subjects as $subject) {
            $isPublished = $this->publicationService->canViewResult(
                $student, $filters->academicYearId, $filters->semesterId, $subject->id
            );

            $components = AssessmentComponent::where('academic_year_id', $filters->academicYearId)
                ->where('subject_id', $subject->id)
                ->where('status', true)
                ->orderBy('order')
                ->get();

            $exams = Exam::where('academic_year_id', $filters->academicYearId)
                ->where('subject_id', $subject->id)
                ->where('section_id', $student->section_id)
                ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
                ->get();

            $examIds = $exams->pluck('id');
            $results = ExamResult::where('student_id', $student->id)
                ->whereIn('exam_id', $examIds)
                ->get();

            $componentScores = [];
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
                $contribution = $pct !== null ? round(($pct * (float)$comp->weight_percentage) / 100, 2) : null;

                $componentScores[] = [
                    'name' => $comp->name,
                    'code' => $comp->code,
                    'obtained' => $obtained,
                    'total' => $total,
                    'percentage' => $pct,
                    'weight' => (float)$comp->weight_percentage,
                    'contribution' => $contribution,
                ];
            }

            $weightedTotal = round(collect($componentScores)->whereNotNull('contribution')->sum('contribution'), 2);
            $gradeInfo = $this->resolveGrade($weightedTotal, $scales);

            $savedGrade = StudentSubjectGrade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $filters->academicYearId)
                ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
                ->first();

            $isPassing = $gradeInfo['is_passing'] ?? null;
            $gpa = $gradeInfo['gpa_point'] ?? null;

            if ($gpa !== null) {
                $totalGpa += $gpa;
                $gpaCount++;
            }
            if ($isPassing === true) $passedCount++;
            if ($isPassing === false) $failedCount++;

            $subjectResults[] = [
                'subject' => $subject,
                'components' => $componentScores,
                'total_percentage' => $weightedTotal,
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
            ? round(collect($subjectResults)->avg('total_percentage'), 2) : 0;

        $overallStatus = 'pending';
        if ($failedCount > 0) $overallStatus = 'failed';
        elseif ($passedCount > 0 && $passedCount == count($subjectResults)) $overallStatus = 'passed';
        elseif ($passedCount > 0) $overallStatus = 'incomplete';

        $canGenerateOfficial = $this->publicationService->canGenerateOfficialReport(
            $filters->academicYearId, $filters->semesterId, $student->section_id
        );

        return [
            'student' => $student,
            'school' => $schoolSettings,
            'subjects' => $subjectResults,
            'verification_uuid' => Str::uuid()->toString(),
            'can_generate_official' => $canGenerateOfficial,
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

    public function getViewTemplate(): string
    {
        return 'reports.student';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->studentId !== null && $filters->academicYearId !== null;
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
