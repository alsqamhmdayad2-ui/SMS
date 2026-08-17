<?php

namespace App\Services;

use App\Models\ResultPublication;
use App\Models\Student;
use App\Models\StudentSemesterMark;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ResultPublicationService
{
    /**
     * Publish results for a section in a specific semester.
     */
    public function publish(array $data, $userId)
    {
        // For the new wizard, we only publish at the section level per semester
        $type = 'section';

        $this->validateMarksComplete(
            $data['academic_year_id'],
            $data['semester_id'],
            $data['section_id']
        );

        $publication = ResultPublication::updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'semester_id' => $data['semester_id'],
                'section_id' => $data['section_id'],
                'published_type' => $type,
            ],
            [
                'grade_id' => $data['grade_id'],
                'status' => 'published',
                'published_by' => $userId,
                'published_at' => Carbon::now(),
                'notes' => $data['notes'] ?? null,
            ]
        );

        // Lock all marks for this section & semester
        StudentSemesterMark::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->update([
                'is_locked' => true,
                'locked_by' => $userId,
                'locked_at' => Carbon::now(),
            ]);

        return $publication;
    }



    /**
     * Check if all subjects in the section have locked/entered marks for all students.
     */
    protected function validateMarksComplete($academicYearId, $semesterId, $sectionId)
    {
        $studentsCount = Student::where('section_id', $sectionId)->count();
        if ($studentsCount === 0) {
            throw ValidationException::withMessages([
                'publish' => 'لا يمكن الاعتماد: لا يوجد طلاب في هذه الشعبة.'
            ]);
        }

        // Find all subjects assigned to this section
        $subjects = Subject::whereHas('sections', fn($q) => $q->where('sections.id', $sectionId))->get();
        if ($subjects->isEmpty()) {
            // Check if assigned at class level
            $section = \App\Models\Section::with('schoolClass.subjects')->find($sectionId);
            $subjects = $section->schoolClass->subjects ?? collect();
        }
            
        if ($subjects->isEmpty()) {
            throw ValidationException::withMessages([
                'publish' => 'لا يمكن الاعتماد: لا توجد مواد دراسية مرتبطة بهذه الشعبة.'
            ]);
        }

        foreach ($subjects as $subject) {
            $marksCount = StudentSemesterMark::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('section_id', $sectionId)
                ->where('subject_id', $subject->id)
                ->count();
            
            if ($marksCount < $studentsCount) {
                throw ValidationException::withMessages([
                    'publish' => 'لا يمكن الاعتماد: الدرجات غير مكتملة في مادة ' . $subject->name
                ]);
            }
        }
    }

    /**
     * Change status to draft/unpublish.
     */
    public function unpublish(ResultPublication $publication)
    {
        $publication->update(['status' => 'draft', 'published_at' => null]);
        
        // Unlock grades
        StudentSemesterMark::where('academic_year_id', $publication->academic_year_id)
            ->where('semester_id', $publication->semester_id)
            ->where('section_id', $publication->section_id)
            ->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null
            ]);

        return $publication;
    }

    /**
     * Core authorization method for viewing results.
     */
    public function canViewResult(Student $student, $academicYearId, $semesterId): bool
    {
        return ResultPublication::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('section_id', $student->section_id)
            ->where('published_type', 'section')
            ->where('status', 'published')
            ->exists();
    }

    /**
     * Check if an official (non-draft) report can be generated.
     */
    public function canGenerateOfficialReport($academicYearId, $semesterId, $sectionId): bool
    {
        return ResultPublication::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('section_id', $sectionId)
            ->where('published_type', 'section')
            ->where('status', 'published')
            ->exists();
    }
}
