<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\StudentSubjectGrade;
use App\Models\GradeScale;
use App\Models\SchoolSetting;

class StatisticsReportService implements ReportInterface
{
    public function getData(ReportFilterData $filters): array
    {
        $schoolSettings = SchoolSetting::first();
        $scales = GradeScale::where('status', true)->orderByDesc('percentage_from')->get();

        // Build base query for grades
        $gradesQuery = StudentSubjectGrade::where('academic_year_id', $filters->academicYearId)
            ->when($filters->semesterId, fn($q) => $q->where('semester_id', $filters->semesterId))
            ->when($filters->sectionId, fn($q) => $q->where('section_id', $filters->sectionId))
            ->when($filters->subjectId, fn($q) => $q->where('subject_id', $filters->subjectId));

        $allGrades = $gradesQuery->get();

        // Grade Distribution
        $gradeDistribution = [];
        foreach ($scales as $scale) {
            $count = $allGrades->filter(fn($g) =>
                (float)$g->total_percentage >= (float)$scale->percentage_from &&
                (float)$g->total_percentage <= (float)$scale->percentage_to
            )->count();
            $gradeDistribution[] = [
                'letter' => $scale->letter_grade,
                'range' => $scale->percentage_from . '-' . $scale->percentage_to . '%',
                'count' => $count,
                'percentage' => $allGrades->count() > 0 ? round($count / $allGrades->count() * 100, 1) : 0,
            ];
        }

        // Pass Rate
        $passingGrades = $allGrades->filter(fn($g) => $g->is_passing);
        $passRate = $allGrades->count() > 0
            ? round($passingGrades->count() / $allGrades->count() * 100, 1) : 0;

        // GPA Statistics
        $gpaValues = $allGrades->pluck('gpa_points')->filter()->map(fn($v) => (float)$v);
        $avgGpa = $gpaValues->count() > 0 ? round($gpaValues->avg(), 2) : 0;

        // Top Subjects (by average percentage)
        $subjectIds = $allGrades->pluck('subject_id')->unique();
        $subjectStats = [];
        foreach ($subjectIds as $subjectId) {
            $subjectGrades = $allGrades->where('subject_id', $subjectId);
            $subject = Subject::find($subjectId);
            if (!$subject) continue;
            
            $subjectAvg = round($subjectGrades->avg('total_percentage'), 2);
            $subjectPassRate = $subjectGrades->count() > 0
                ? round($subjectGrades->where('is_passing', true)->count() / $subjectGrades->count() * 100, 1) : 0;

            $subjectStats[] = [
                'subject' => $subject,
                'average' => $subjectAvg,
                'pass_rate' => $subjectPassRate,
                'total_students' => $subjectGrades->count(),
            ];
        }

        // Top/Weakest subjects
        usort($subjectStats, fn($a, $b) => $b['average'] <=> $a['average']);
        $topSubjects = array_slice($subjectStats, 0, 5);
        $weakestSubjects = array_slice(array_reverse($subjectStats), 0, 5);

        // Honor Students (Top 10 by GPA)
        $studentGpas = $allGrades->groupBy('student_id')->map(function ($grades, $studentId) {
            return [
                'student_id' => $studentId,
                'gpa' => round($grades->avg('gpa_points'), 2),
                'average' => round($grades->avg('total_percentage'), 2),
                'subjects_count' => $grades->count(),
                'passed' => $grades->where('is_passing', true)->count(),
                'failed' => $grades->where('is_passing', false)->count(),
            ];
        })->sortByDesc('gpa')->values();

        $honorStudents = $studentGpas->take(10)->map(function ($data) {
            $data['student'] = Student::find($data['student_id']);
            return $data;
        });

        // Failed Students
        $failedStudents = $studentGpas->filter(fn($s) => $s['failed'] > 0)->map(function ($data) {
            $data['student'] = Student::find($data['student_id']);
            return $data;
        })->values();

        return [
            'school' => $schoolSettings,
            'grade_distribution' => $gradeDistribution,
            'pass_rate' => $passRate,
            'average_gpa' => $avgGpa,
            'top_subjects' => $topSubjects,
            'weakest_subjects' => $weakestSubjects,
            'honor_students' => $honorStudents,
            'failed_students' => $failedStudents,
            'overview' => [
                'total_grades' => $allGrades->count(),
                'total_students' => $allGrades->pluck('student_id')->unique()->count(),
                'total_subjects' => $subjectIds->count(),
                'highest_percentage' => $allGrades->max('total_percentage') ?? 0,
                'lowest_percentage' => $allGrades->min('total_percentage') ?? 0,
            ],
        ];
    }

    public function getViewTemplate(): string
    {
        return 'reports.statistics';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->academicYearId !== null;
    }
}
