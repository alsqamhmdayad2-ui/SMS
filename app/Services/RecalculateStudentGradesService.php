<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class RecalculateStudentGradesService
{
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Recalculate grades.
     * Supports granular filters: student_id, section_id, subject_id, semester_id, academic_year_id
     */
    public function recalculate(array $filters = [])
    {
        // Get all students who have results in this subject for this year/semester
        $query = DB::table('exam_results')
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->select(
                'exam_results.student_id', 
                'exams.subject_id', 
                'exams.academic_year_id', 
                'exams.semester_id', 
                'exams.section_id'
            )
            ->distinct();

        if (!empty($filters['student_id'])) {
            $query->where('exam_results.student_id', $filters['student_id']);
        }
        if (!empty($filters['section_id'])) {
            $query->where('exams.section_id', $filters['section_id']);
        }
        if (!empty($filters['subject_id'])) {
            $query->where('exams.subject_id', $filters['subject_id']);
        }
        if (!empty($filters['semester_id'])) {
            $query->where('exams.semester_id', $filters['semester_id']);
        }
        if (!empty($filters['academic_year_id'])) {
            $query->where('exams.academic_year_id', $filters['academic_year_id']);
        }

        $records = $query->get();

        if ($records->count() > 500) {
            // Dispatch to Queue if processing large batches
            // \App\Jobs\RecalculateGradesBatchJob::dispatch($records->toArray());
            // return 'Queued ' . $records->count() . ' calculations.';
        }

        $count = 0;
        foreach ($records as $record) {
            $this->gradeCalculationService->calculateSubjectGrade(
                $record->student_id,
                $record->subject_id,
                $record->academic_year_id,
                $record->semester_id,
                $record->section_id
            );
            $count++;
        }

        return $count;
    }
}
