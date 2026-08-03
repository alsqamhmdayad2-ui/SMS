<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\AssessmentComponent;
use App\Models\StudentSubjectGrade;
use App\Models\GradeScale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradebookService
{
    /**
     * Get the full gradebook for a subject/section/semester/year.
     * Returns a collection of students, each with their component scores and final grade.
     */
    public function getGradebook($academicYearId, $semesterId, $subjectId, $sectionId): Collection
    {
        // 1. Get assessment components for this subject/year
        $components = AssessmentComponent::where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->where('status', true)
            ->orderBy('order')
            ->get();

        // 2. Get students in this section
        $students = Student::where('section_id', $sectionId)
            ->orderBy('name')
            ->get();

        // 3. Get all exams for this subject/section/year/semester
        $examsQuery = Exam::where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId);
        if ($semesterId) {
            $examsQuery->where('semester_id', $semesterId);
        }
        $exams = $examsQuery->get();
        $examIds = $exams->pluck('id');

        // 4. Get all results for these exams
        $results = ExamResult::whereIn('exam_id', $examIds)
            ->get()
            ->groupBy('student_id');

        // 5. Get saved subject grades (snapshot)
        $subjectGrades = StudentSubjectGrade::where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get()
            ->keyBy('student_id');

        // 6. Grade scales
        $scales = GradeScale::where('status', true)->orderByDesc('percentage_from')->get();

        // 7. Build gradebook rows
        $gradebook = $students->map(function ($student) use ($components, $exams, $results, $subjectGrades, $scales) {
            $studentResults = $results->get($student->id, collect());
            $componentScores = [];

            foreach ($components as $comp) {
                // Find exams matching this component code (exam type matches component code)
                $compExams = $exams->filter(fn($e) => strtoupper($e->type) === strtoupper($comp->code));
                
                $totalObtained = 0;
                $totalMax = 0;
                $examDetails = [];

                foreach ($compExams as $exam) {
                    $result = $studentResults->firstWhere('exam_id', $exam->id);
                    if ($result && $result->attendance_status === 'present' && $result->marks_obtained !== null) {
                        $totalObtained += (float)$result->marks_obtained;
                        $totalMax += (float)$result->total_marks;
                        $examDetails[] = [
                            'exam_title' => $exam->title,
                            'obtained' => (float)$result->marks_obtained,
                            'total' => (float)$result->total_marks,
                            'percentage' => (float)$result->percentage,
                        ];
                    } elseif ($result) {
                        $totalMax += (float)$result->total_marks;
                        $examDetails[] = [
                            'exam_title' => $exam->title,
                            'obtained' => 0,
                            'total' => (float)$result->total_marks,
                            'percentage' => 0,
                            'status' => $result->attendance_status,
                        ];
                    }
                }

                $compPercentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null;
                $contribution = $compPercentage !== null ? round(($compPercentage * (float)$comp->weight_percentage) / 100, 2) : null;

                $componentScores[] = [
                    'component' => $comp,
                    'obtained' => $totalObtained,
                    'total' => $totalMax,
                    'percentage' => $compPercentage,
                    'weight' => (float)$comp->weight_percentage,
                    'contribution' => $contribution,
                    'exams' => $examDetails,
                ];
            }

            // Calculate weighted total
            $weightedTotal = collect($componentScores)
                ->whereNotNull('contribution')
                ->sum('contribution');

            // Get saved grade or resolve dynamically
            $savedGrade = $subjectGrades->get($student->id);
            $gradeInfo = $this->resolveFromScales($weightedTotal, $scales);

            return [
                'student' => $student,
                'components' => $componentScores,
                'total_percentage' => round($weightedTotal, 2),
                'letter_grade' => $savedGrade->letter_grade ?? ($gradeInfo['letter_grade'] ?? null),
                'gpa_points' => $savedGrade ? (float)$savedGrade->gpa_points : ($gradeInfo['gpa_point'] ?? null),
                'is_passing' => $savedGrade ? $savedGrade->is_passing : ($gradeInfo['is_passing'] ?? null),
                'rank' => $savedGrade->rank_in_section ?? null,
                'is_finalized' => $savedGrade->is_finalized ?? false,
            ];
        });

        // Sort by total descending and assign ranks
        $gradebook = $gradebook->sortByDesc('total_percentage')->values();
        $rank = 1;
        foreach ($gradebook as &$row) {
            $row['rank'] = $rank++;
        }

        return $gradebook;
    }

    /**
     * Get detailed breakdown for a single student.
     */
    public function getStudentBreakdown($studentId, $academicYearId, $semesterId, $subjectId, $sectionId): ?array
    {
        $gradebook = $this->getGradebook($academicYearId, $semesterId, $subjectId, $sectionId);
        return $gradebook->firstWhere('student.id', $studentId);
    }

    /**
     * Get class statistics.
     */
    public function getClassStats(Collection $gradebook): array
    {
        $scores = $gradebook->pluck('total_percentage')->filter(fn($v) => $v > 0);

        return [
            'total_students' => $gradebook->count(),
            'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
            'highest' => $scores->isNotEmpty() ? round($scores->max(), 1) : 0,
            'lowest' => $scores->isNotEmpty() ? round($scores->min(), 1) : 0,
            'median' => $scores->isNotEmpty() ? round($scores->median(), 1) : 0,
            'pass_count' => $gradebook->where('is_passing', true)->count(),
            'fail_count' => $gradebook->where('is_passing', false)->count(),
            'pass_rate' => $gradebook->count() > 0
                ? round(($gradebook->where('is_passing', true)->count() / $gradebook->count()) * 100, 1)
                : 0,
        ];
    }

    protected function resolveFromScales($percentage, $scales): array
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
