<?php

namespace App\DTOs;

readonly class ReportFilterData
{
    public function __construct(
        public ?int $academicYearId = null,
        public ?int $semesterId = null,
        public ?int $gradeId = null,
        public ?int $schoolClassId = null,
        public ?int $sectionId = null,
        public ?int $subjectId = null,
        public ?int $teacherId = null,
        public ?int $studentId = null,
        public ?string $status = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            academicYearId: isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : null,
            semesterId: isset($data['semester_id']) ? (int) $data['semester_id'] : null,
            gradeId: isset($data['grade_id']) ? (int) $data['grade_id'] : null,
            schoolClassId: isset($data['class_id']) ? (int) $data['class_id'] : null,
            sectionId: isset($data['section_id']) ? (int) $data['section_id'] : null,
            subjectId: isset($data['subject_id']) ? (int) $data['subject_id'] : null,
            teacherId: isset($data['teacher_id']) ? (int) $data['teacher_id'] : null,
            studentId: isset($data['student_id']) ? (int) $data['student_id'] : null,
            status: $data['status'] ?? null,
        );
    }
}
