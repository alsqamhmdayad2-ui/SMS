<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\AssessmentComponent;
use App\Models\SchoolSetting;
use App\Services\ResultPublicationService;

class SubjectReportService implements ReportInterface
{
    public function __construct(
        protected ResultPublicationService $publicationService
    ) {}

    public function getData(ReportFilterData $filters): array
    {
        $subject = Subject::findOrFail($filters->subjectId);
        $students = Student::where('section_id', $filters->sectionId)->orderBy('name')->get();
        $schoolSettings = SchoolSetting::first();

        $components = AssessmentComponent::where('academic_year_id', $filters->academicYearId)
            ->where('subject_id', $filters->subjectId)
            ->where('status', true)
            ->orderBy('order')
            ->get();

        $exams = Exam::where('academic_year_id', $filters->academicYearId)
            ->where('subject_id', $filters->subjectId)
            ->where('section_id', $filters->sectionId)
            ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
            ->get();

        $examIds = $exams->pluck('id');

        $studentsData = [];
        $totalPercentages = [];

        foreach ($students as $student) {
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

                $componentScores[$comp->code] = [
                    'obtained' => $obtained,
                    'total' => $total,
                    'percentage' => $pct,
                    'contribution' => $contribution,
                ];
            }

            $weightedTotal = round(collect($componentScores)->whereNotNull('contribution')->sum('contribution'), 2);
            $totalPercentages[] = $weightedTotal;

            $studentsData[] = [
                'student' => $student,
                'components' => $componentScores,
                'total' => $weightedTotal,
            ];
        }

        // Sort by total descending and assign rank
        usort($studentsData, fn($a, $b) => $b['total'] <=> $a['total']);
        $rank = 1;
        foreach ($studentsData as &$sd) {
            $sd['rank'] = $rank++;
        }

        $isPublished = $filters->sectionId
            ? $this->publicationService->isPublished($filters->academicYearId, $filters->sectionId, $filters->subjectId)
            : false;

        return [
            'school' => $schoolSettings,
            'subject' => $subject,
            'components' => $components,
            'students' => $studentsData,
            'is_published' => $isPublished,
            'statistics' => [
                'total_students' => count($studentsData),
                'highest' => count($totalPercentages) > 0 ? max($totalPercentages) : 0,
                'lowest' => count($totalPercentages) > 0 ? min($totalPercentages) : 0,
                'average' => count($totalPercentages) > 0 ? round(array_sum($totalPercentages) / count($totalPercentages), 2) : 0,
            ],
        ];
    }

    public function getViewTemplate(): string
    {
        return 'reports.subject';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->subjectId !== null && $filters->sectionId !== null && $filters->academicYearId !== null;
    }
}
