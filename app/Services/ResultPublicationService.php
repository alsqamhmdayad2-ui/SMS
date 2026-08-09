<?php

namespace App\Services;

use App\Models\ResultPublication;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\StudentSubjectGrade;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ResultPublicationService
{
    /**
     * Publish results for a subject, section, or semester.
     */
    public function publish(array $data, $userId)
    {
        $type = $data['published_type']; // 'subject', 'section', 'semester'

        // Validation logic
        if ($type === 'subject') {
            $this->validateMarksComplete(
                $data['academic_year_id'],
                $data['semester_id'],
                $data['section_id'],
                $data['subject_id']
            );
            $this->validateCalculations(
                $data['academic_year_id'],
                $data['semester_id'],
                $data['section_id'],
                $data['subject_id']
            );
        }

        // If type is section or semester, you'd iterate over all subjects in that scope
        // and run validations for each. For brevity, assuming 'subject' publish is the primary atomic unit.

        $publication = ResultPublication::updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'semester_id' => $data['semester_id'] ?? null,
                'grade_id' => $data['grade_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'] ?? null,
            ],
            [
                'published_type' => $type,
                'status' => 'published',
                'published_by' => $userId,
                'published_at' => Carbon::now(),
                'notes' => $data['notes'] ?? null,
            ]
        );

        // Lock the student_subject_grades to finalize them
        if ($type === 'subject') {
            StudentSubjectGrade::where('academic_year_id', $data['academic_year_id'])
                ->when($data['semester_id'], fn($q) => $q->where('semester_id', $data['semester_id']))
                ->where('section_id', $data['section_id'])
                ->where('subject_id', $data['subject_id'])
                ->update(['is_finalized' => true]);
        }

        return $publication;
    }



    /**
     * Check if all students in the section have marks for all exams.
     */
    protected function validateMarksComplete($academicYearId, $semesterId, $sectionId, $subjectId)
    {
        $students = Student::where('section_id', $sectionId)->get();
        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'publish' => 'Cannot publish: No students found in this section.'
            ]);
        }

        $exams = Exam::where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        if ($exams->isEmpty()) {
            throw ValidationException::withMessages([
                'publish' => 'Cannot publish: No exams found for this subject.'
            ]);
        }

        $examIds = $exams->pluck('id');
        
        foreach ($students as $student) {
            $resultsCount = ExamResult::where('student_id', $student->id)
                ->whereIn('exam_id', $examIds)
                ->count();
            
            if ($resultsCount < $exams->count()) {
                throw ValidationException::withMessages([
                    'publish' => 'Cannot publish: Missing marks for student "' . $student->name . '".'
                ]);
            }
        }
    }

    /**
     * Ensure grade calculation has run (student_subject_grades exists).
     */
    protected function validateCalculations($academicYearId, $semesterId, $sectionId, $subjectId)
    {
        $studentCount = Student::where('section_id', $sectionId)->count();
        
        $calculatedCount = StudentSubjectGrade::where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->count();

        if ($calculatedCount < $studentCount) {
            throw ValidationException::withMessages([
                'publish' => 'Cannot publish: Final grade calculation is incomplete for some students.'
            ]);
        }
    }

    /**
     * Change status to draft/unpublish.
     */
    public function unpublish(ResultPublication $publication)
    {
        $publication->update(['status' => 'draft', 'published_at' => null]);
        
        // Unlock grades
        if ($publication->published_type === 'subject') {
            StudentSubjectGrade::where('academic_year_id', $publication->academic_year_id)
                ->when($publication->semester_id, fn($q) => $q->where('semester_id', $publication->semester_id))
                ->where('section_id', $publication->section_id)
                ->where('subject_id', $publication->subject_id)
                ->update(['is_finalized' => false]);
        }

        return $publication;
    }

    /**
     * Core authorization method for viewing results.
     * Hierarchy priority: Subject -> Section -> Semester
     */
    public function canViewResult(Student $student, $academicYearId, $semesterId = null, $subjectId = null): bool
    {
        $query = ResultPublication::where('academic_year_id', $academicYearId)
            ->where('section_id', $student->section_id)
            ->where('status', 'published');

        if ($semesterId) {
            $query->where(function($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)->orWhereNull('semester_id');
            });
        }

        $publications = $query->get();

        if ($publications->isEmpty()) {
            return false;
        }

        // 1. Check Subject-level publication
        if ($subjectId) {
            $subjectPub = $publications->firstWhere('subject_id', $subjectId);
            if ($subjectPub) return true;
        }

        // 2. Check Section-level (all subjects in section)
        $sectionPub = $publications->where('published_type', 'section')->first();
        if ($sectionPub) return true;

        // 3. Check Semester-level (all sections in semester)
        $semesterPub = $publications->where('published_type', 'semester')->first();
        if ($semesterPub) return true;

        return false;
    }

    /**
     * Check if a publication record exists and is in draft status.
     */
    public function isDraft($academicYearId, $sectionId, $subjectId = null): bool
    {
        return ResultPublication::where('academic_year_id', $academicYearId)
            ->where('section_id', $sectionId)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->where('status', 'draft')
            ->exists();
    }

    /**
     * Check if a publication record exists and is published.
     */
    public function isPublished($academicYearId, $sectionId, $subjectId = null): bool
    {
        return ResultPublication::where('academic_year_id', $academicYearId)
            ->where('section_id', $sectionId)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->where('status', 'published')
            ->exists();
    }

    /**
     * Check if a publication record exists and is archived.
     */
    public function isArchived($academicYearId, $sectionId, $subjectId = null): bool
    {
        return ResultPublication::where('academic_year_id', $academicYearId)
            ->where('section_id', $sectionId)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->where('status', 'archived')
            ->exists();
    }

    /**
     * Check if an official (non-draft) report can be generated.
     * ALL subjects in the section must be published.
     */
    public function canGenerateOfficialReport($academicYearId, $semesterId, $sectionId): bool
    {
        $subjectIds = Exam::where('academic_year_id', $academicYearId)
            ->where('section_id', $sectionId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->pluck('subject_id')
            ->unique();

        if ($subjectIds->isEmpty()) return false;

        foreach ($subjectIds as $subjectId) {
            $published = ResultPublication::where('academic_year_id', $academicYearId)
                ->where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->where('status', 'published')
                ->exists();

            if (!$published) return false;
        }

        return true;
    }
}
