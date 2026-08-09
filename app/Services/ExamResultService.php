<?php

namespace App\Services;

use App\Models\ExamResult;
use App\Models\Exam;
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
     * Resolve letter grade from percentage using hardcoded scale.
     */
    public function resolveGrade($percentage): ?array
    {
        if ($percentage === null) return null;

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
