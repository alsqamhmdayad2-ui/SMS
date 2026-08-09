<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\StudentSubjectGrade;
use App\Models\SchoolSetting;

/**
 * Annual Report Service
 * Compiles a full-year academic report for a student or section
 * across all semesters in a given academic year.
 */
class AnnualReportService implements ReportInterface
{
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

    public function getData(ReportFilterData $filters): array
    {
        $school = SchoolSetting::first();

        // Get all semesters for this academic year
        $semesters = Semester::all();

        // Decide scope: single student or section
        if ($filters->studentId) {
            return $this->getStudentAnnualReport($filters, $semesters, $school);
        }

        return $this->getSectionAnnualReport($filters, $semesters, $school);
    }

    protected function getStudentAnnualReport(ReportFilterData $filters, $semesters, $school): array
    {
        $student = Student::with(['grade', 'schoolClass', 'section'])->findOrFail($filters->studentId);

        $subjectIds = Exam::where('academic_year_id', $filters->academicYearId)
            ->where('section_id', $student->section_id)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $semesterData = [];

        foreach ($semesters as $semester) {
            $semesterRows = [];
            $semTotalPct = 0;
            $semCount = 0;

            foreach ($subjects as $subject) {
                $grade = StudentSubjectGrade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $filters->academicYearId)
                    ->where('semester_id', $semester->id)
                    ->first();

                $pct = $grade ? (float) $grade->total_percentage : null;
                $gradeInfo = $pct !== null ? $this->resolveGrade($pct) : [];

                if ($pct !== null) {
                    $semTotalPct += $pct;
                    $semCount++;
                }

                $semesterRows[] = [
                    'subject'      => $subject,
                    'percentage'   => $pct,
                    'letter_grade' => $gradeInfo['letter_grade'] ?? '-',
                    'is_passing'   => $gradeInfo['is_passing'] ?? null,
                    'gpa_point'    => $gradeInfo['gpa_point'] ?? null,
                ];
            }

            $semAvg = $semCount > 0 ? round($semTotalPct / $semCount, 2) : null;
            $semGradeInfo = $semAvg !== null ? $this->resolveGrade($semAvg) : [];

            $semesterData[] = [
                'semester'     => $semester,
                'subjects'     => $semesterRows,
                'average'      => $semAvg,
                'letter_grade' => $semGradeInfo['letter_grade'] ?? '-',
                'is_passing'   => $semGradeInfo['is_passing'] ?? null,
            ];
        }

        // Annual overall
        $allSemAverages = collect($semesterData)->pluck('average')->filter()->values();
        $annualAvg = $allSemAverages->isNotEmpty() ? round($allSemAverages->avg(), 2) : null;
        $annualGradeInfo = $annualAvg !== null ? $this->resolveGrade($annualAvg) : [];
        $failedSubjects = collect($semesterData)->flatMap(fn($s) => collect($s['subjects'])->where('is_passing', false));

        return [
            'type'          => 'student',
            'student'       => $student,
            'subjects'      => $subjects,
            'semesters'     => $semesterData,
            'annual_avg'    => $annualAvg,
            'annual_grade'  => $annualGradeInfo['letter_grade'] ?? '-',
            'annual_pass'   => ($annualGradeInfo['is_passing'] ?? null),
            'failed_count'  => $failedSubjects->count(),
            'school'        => $school,
            'generated_at'  => now()->format('Y-m-d H:i'),
        ];
    }

    protected function getSectionAnnualReport(ReportFilterData $filters, $semesters, $school): array
    {
        $students = Student::where('section_id', $filters->sectionId)
            ->orderBy('first_name')
            ->get();

        $subjectIds = Exam::where('academic_year_id', $filters->academicYearId)
            ->where('section_id', $filters->sectionId)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $studentsData = [];

        foreach ($students as $student) {
            $semesterAverages = [];

            foreach ($semesters as $semester) {
                $semPcts = [];
                foreach ($subjects as $subject) {
                    $grade = StudentSubjectGrade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('academic_year_id', $filters->academicYearId)
                        ->where('semester_id', $semester->id)
                        ->first();

                    if ($grade && $grade->total_percentage !== null) {
                        $semPcts[] = (float) $grade->total_percentage;
                    }
                }
                $semAvg = count($semPcts) > 0 ? round(array_sum($semPcts) / count($semPcts), 2) : null;
                $semesterAverages[$semester->id] = $semAvg;
            }

            $validAvgs = array_filter($semesterAverages, fn($a) => $a !== null);
            $annualAvg = count($validAvgs) > 0 ? round(array_sum($validAvgs) / count($validAvgs), 2) : null;
            $annualGradeInfo = $annualAvg !== null ? $this->resolveGrade($annualAvg) : [];

            $studentsData[] = [
                'student'           => $student,
                'semester_averages' => $semesterAverages,
                'annual_avg'        => $annualAvg,
                'letter_grade'      => $annualGradeInfo['letter_grade'] ?? '-',
                'is_passing'        => $annualGradeInfo['is_passing'] ?? null,
                'gpa_point'         => $annualGradeInfo['gpa_point'] ?? null,
            ];
        }

        usort($studentsData, fn($a, $b) => ($b['annual_avg'] ?? 0) <=> ($a['annual_avg'] ?? 0));
        foreach ($studentsData as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return [
            'type'         => 'section',
            'students'     => $studentsData,
            'subjects'     => $subjects,
            'semesters'    => $semesters,
            'school'       => $school,
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    public function getViewTemplate(): string
    {
        return 'panels.admin.reports.annual';
    }

    public function validateAccess(ReportFilterData $filters): bool
    {
        return $filters->academicYearId !== null && ($filters->studentId !== null || $filters->sectionId !== null);
    }
}
