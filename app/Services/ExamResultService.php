<?php

namespace App\Services;

use App\Models\ExamResult;
use App\Models\Exam;
use App\Models\GradeScale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamResultService
{
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Save a single student mark (AJAX auto-save from Excel grid).
     */
    public function saveSingleMark(Exam $exam, array $data, $userId): ExamResult
    {
        return DB::transaction(function () use ($exam, $data, $userId) {
            $studentId = $data['student_id'];
            $marksObtained = $data['marks_obtained'] ?? null;
            $totalMarks = $exam->total_marks;
            $attendanceStatus = $data['attendance_status'] ?? 'present';

            // Calculate percentage
            $percentage = null;
            if ($marksObtained !== null && $totalMarks > 0 && $attendanceStatus === 'present') {
                $percentage = round(($marksObtained / $totalMarks) * 100, 2);
            }

            $result = ExamResult::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $studentId,
                ],
                [
                    'marks_obtained' => $attendanceStatus === 'present' ? $marksObtained : null,
                    'total_marks' => $totalMarks,
                    'percentage' => $percentage,
                    'attendance_status' => $attendanceStatus,
                    'remarks' => $data['remarks'] ?? null,
                    'graded_at' => Carbon::now(),
                ]
            );

            // Audit trail
            if ($result->wasRecentlyCreated || is_null($result->graded_by)) {
                $result->graded_by = $userId;
            } else {
                $result->updated_by = $userId;
            }
            $result->save();

            // Trigger grade recalculation for this student
            $this->gradeCalculationService->calculateSubjectGrade(
                $studentId,
                $exam->subject_id,
                $exam->academic_year_id,
                $exam->semester_id,
                $exam->section_id
            );

            return $result;
        });
    }

    /**
     * Bulk save marks for an exam (Excel-like entry).
     */
    public function bulkSaveMarks(Exam $exam, array $resultsData, $userId)
    {
        return DB::transaction(function () use ($exam, $resultsData, $userId) {
            $updatedResults = [];

            foreach ($resultsData as $data) {
                $updatedResults[] = $this->saveSingleMark($exam, $data, $userId);
            }

            return $updatedResults;
        });
    }

    /**
     * Resolve letter grade from percentage using active GradeScale.
     */
    public function resolveGrade($percentage): ?array
    {
        if ($percentage === null) return null;

        $scale = GradeScale::where('status', true)
            ->where('percentage_from', '<=', $percentage)
            ->where('percentage_to', '>=', $percentage)
            ->first();

        if (!$scale) return null;

        return [
            'letter_grade' => $scale->letter_grade,
            'gpa_point' => (float)$scale->gpa_point,
            'is_passing' => $scale->is_passing,
        ];
    }
}
