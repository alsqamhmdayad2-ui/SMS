<?php

namespace App\Services;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function getAll($filters = [])
    {
        $query = Exam::with(['academicYear', 'semester', 'schoolClass', 'sections', 'subject', 'teacher'])
            ->latest();

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['semester_id'])) {
            $query->where('semester_id', $filters['semester_id']);
        }
        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }
        if (!empty($filters['section_id'])) {
            $query->whereHas('sections', function ($q) use ($filters) {
                $q->where('sections.id', $filters['section_id']);
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15)->withQueryString();
    }

    public function create(array $data)
    {
        $data['status'] = $data['status'] ?? ExamStatus::DRAFT->value;
        $this->checkForConflicts($data);
        
        $exam = Exam::create($data);
        if (isset($data['section_ids'])) {
            $exam->sections()->sync($data['section_ids']);
        }
        
        return $exam;
    }

    public function update(Exam $exam, array $data)
    {
        if ($exam->status !== ExamStatus::DRAFT) {
            throw ValidationException::withMessages(['status' => 'Cannot modify a locked exam.']);
        }

        $this->checkForConflicts($data, $exam->id);
        $exam->update($data);
        if (isset($data['section_ids'])) {
            $exam->sections()->sync($data['section_ids']);
        }
        
        return $exam;
    }

    public function delete(Exam $exam)
    {
        if ($exam->status !== ExamStatus::DRAFT) {
            throw ValidationException::withMessages(['status' => 'Cannot delete a locked exam.']);
        }
        return $exam->delete();
    }

    public function publish(Exam $exam)
    {
        if ($exam->total_marks <= 0) {
            throw ValidationException::withMessages(['status' => 'Cannot publish an exam with 0 total marks.']);
        }
        if ($exam->questions_count === 0) {
            throw ValidationException::withMessages(['status' => 'Cannot publish an exam with no questions.']);
        }

        $exam->update(['status' => ExamStatus::PUBLISHED->value]);
        return $exam;
    }

    /**
     * Prevent scheduling conflicts.
     * 1. Teacher cannot be in two exams at the same time.
     * 2. Section cannot have two exams at the same time.
     */
    protected function checkForConflicts(array $data, $ignoreId = null)
    {
        $startTime = \Carbon\Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = \Carbon\Carbon::parse($data['end_time'])->format('H:i:s');

        $query = Exam::whereDate('exam_date', $data['exam_date'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        // Teacher conflict
        $teacherConflict = (clone $query)->where('teacher_id', $data['teacher_id'])->exists();
        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => ['The selected teacher is already scheduled for an exam at this time.'],
            ]);
        }

        // Section conflict
        if (!empty($data['section_ids'])) {
            $sectionConflict = (clone $query)->whereHas('sections', function ($q) use ($data) {
                $q->whereIn('sections.id', $data['section_ids']);
            })->exists();
            
            if ($sectionConflict) {
                throw ValidationException::withMessages([
                    'section_ids' => ['One or more selected sections already have an exam at this time.'],
                ]);
            }
        }


    }
}
