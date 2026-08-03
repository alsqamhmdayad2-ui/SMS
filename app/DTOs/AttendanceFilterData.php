<?php

namespace App\DTOs;

/**
 * Immutable filter data for all attendance queries.
 * Prevents accidental mutation of filters during report generation.
 */
readonly class AttendanceFilterData
{
    public function __construct(
        public ?int    $academicYearId  = null,
        public ?int    $semesterId      = null,
        public ?int    $sectionId       = null,
        public ?int    $gradeId         = null,
        public ?int    $teacherId       = null,
        public ?int    $subjectId       = null,
        public ?int    $studentId       = null,
        public ?string $date            = null,
        public ?string $dateFrom        = null,
        public ?string $dateTo          = null,
        public ?string $month           = null,   // format: Y-m
        public ?string $status          = null,   // session status: open|locked
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            academicYearId: isset($data['academic_year_id']) && $data['academic_year_id'] !== '' ? (int) $data['academic_year_id'] : null,
            semesterId:     isset($data['semester_id'])      && $data['semester_id']      !== '' ? (int) $data['semester_id']      : null,
            sectionId:      isset($data['section_id'])       && $data['section_id']       !== '' ? (int) $data['section_id']       : null,
            gradeId:        isset($data['grade_id'])         && $data['grade_id']         !== '' ? (int) $data['grade_id']         : null,
            teacherId:      isset($data['teacher_id'])       && $data['teacher_id']       !== '' ? (int) $data['teacher_id']       : null,
            subjectId:      isset($data['subject_id'])       && $data['subject_id']       !== '' ? (int) $data['subject_id']       : null,
            studentId:      isset($data['student_id'])       && $data['student_id']       !== '' ? (int) $data['student_id']       : null,
            date:           $data['date']       ?? null,
            dateFrom:       $data['date_from']  ?? null,
            dateTo:         $data['date_to']    ?? null,
            month:          $data['month']      ?? null,
            status:         $data['status']     ?? null,
        );
    }
}
