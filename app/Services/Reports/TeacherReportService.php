<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\StudentSubjectGrade;
use App\Models\GradeScale;
use App\Models\SchoolSetting;
use App\Models\Teacher;

class TeacherReportService implements ReportInterface
{
    public function getData(ReportFilterData $filters): array
    {
        $teacher = Teacher::findOrFail($filters->teacherId);
        $schoolSettings = SchoolSetting::first();
        $scales = GradeScale::where('status', true)->orderByDesc('percentage_from')->get();

        // Get all exams assigned to this teacher
        $exams = Exam::where('teacher_id', $filters->teacherId)
            ->where('academic_year_id', $filters->academicYearId)
            ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
            ->when($filters->sectionId, fn($q) => $q->where('section_id', $filters->sectionId))
            ->get();

        // Group by subject+section
        $grouped = $exams->groupBy(fn($e) => $e->subject_id . '-' . $e->section_id);

        $sectionSubjects = [];

        foreach ($grouped as $key => $groupedExams) {
            [$subjectId, $sectionId] = explode('-', $key);
            $subject = Subject::find($subjectId);

            $students = Student::where('section_id', $sectionId)->orderBy('name')->get();
            
            $studentResults = [];
            $percentages = [];

            foreach ($students as $student) {
                $savedGrade = StudentSubjectGrade::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $filters->academicYearId)
                    ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
                    ->first();

                $pct = $savedGrade ? (float)$savedGrade->total_percentage : null;
                $gradeInfo = $pct !== null ? $this->resolveGrade($pct, $scales) : [];

                if ($pct !== null) $percentages[] = $pct;

                $studentResults[] = [
                    'student' => $student,
                    'percentage' => $pct,
                    'letter_grade' => $gradeInfo['letter_grade'] ?? '-',
                    'gpa' => $gradeInfo['gpa_point'] ?? null,
                    'status' => isset($gradeInfo['is_passing']) ? ($gradeInfo['is_passing'] ? 'pass' : 'fail') : 'pending',
                ];
            }

            // Sort by percentage descending
            usort($studentResults, fn($a, $b) => ($b['percentage'] ?? 0) <=> ($a['percentage'] ?? 0));
            $rank = 1;
            foreach ($studentResults as &$sr) {
                $sr['rank'] = $rank++;
            }

            $sectionSubjects[] = [
                'subject' => $subject,
                'section_id' => $sectionId,
                'students' => $studentResults,
                'statistics' => [
                    'total' => count($studentResults),
                    'highest' => count($percentages) > 0 ? max($percentages) : 0,
                    'lowest' => count($percentages) > 0 ? min($percentages) : 0,
                    'average' => count($percentages) > 0 ? round(array_sum($percentages) / count($percentages), 2) : 0,
                    'pass_rate' => count($studentResults) > 0
                        ? round(collect($studentResults)->where('status', 'pass')->count() / count($studentResults) * 100, 1)
                        : 0,
                ],
            ];
        }

        return [
            'school' => $schoolSettings,
            'teacher' => $teacher,
            'sections' => $sectionSubjects,
        ];
    }

    public function getViewTemplate(): string
    {
        return 'reports.teacher';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->teacherId !== null && $filters->academicYearId !== null;
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
