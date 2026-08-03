<?php

namespace App\Services;

use App\Models\Timetable;
use Illuminate\Validation\ValidationException;

class TimetableService
{
    public function getAll()
    {
        return Timetable::with(['grade', 'schoolClass', 'section', 'subject', 'teacher', 'academicYear', 'semester'])
            ->latest()
            ->get();
    }

    public function getBySection($sectionId)
    {
        return Timetable::with(['subject', 'teacher'])
            ->where('section_id', $sectionId)
            ->where('status', true)
            ->get();
    }

    public function getByTeacher($teacherId)
    {
        return Timetable::with(['grade', 'schoolClass', 'section', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('status', true)
            ->get();
    }

    public function getTodaySchedule($sectionId)
    {
        $today = date('l'); // 'Sunday', 'Monday', etc.
        return Timetable::with(['subject', 'teacher'])
            ->where('section_id', $sectionId)
            ->where('day_of_week', $today)
            ->where('status', true)
            ->orderBy('period_number')
            ->get();
    }

    public function create(array $data)
    {
        $this->checkForConflicts($data);
        return Timetable::create($data);
    }

    public function update(Timetable $timetable, array $data)
    {
        $this->checkForConflicts($data, $timetable->id);
        $timetable->update($data);
        return $timetable;
    }

    public function delete(Timetable $timetable)
    {
        return $timetable->delete();
    }

    /**
     * Prevent scheduling conflicts.
     * 1. Teacher cannot be in two places at the same time.
     * 2. Section cannot have two subjects at the same time.
     * 
     * Uses Primary Check: day_of_week + period_number
     * AND Secondary Check: start_time + end_time overlaps.
     */
    protected function checkForConflicts(array $data, $ignoreId = null)
    {
        $query = Timetable::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                // Primary check: same period
                $q->where('period_number', $data['period_number'])
                  // Secondary check: time overlap
                  ->orWhere(function ($subQ) use ($data) {
                      $subQ->whereTime('start_time', '<', $data['end_time'])
                           ->whereTime('end_time', '>', $data['start_time']);
                  });
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        // Clone query to check teacher conflict
        $teacherConflictQuery = clone $query;
        $teacherConflict = $teacherConflictQuery->where('teacher_id', $data['teacher_id'])->exists();

        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => ['The selected teacher is already scheduled for this period or time on this day.'],
            ]);
        }

        // Clone query to check section conflict
        $sectionConflictQuery = clone $query;
        $sectionConflict = $sectionConflictQuery->where('section_id', $data['section_id'])->exists();

        if ($sectionConflict) {
            throw ValidationException::withMessages([
                'section_id' => ['The selected section already has a class scheduled for this period or time on this day.'],
            ]);
        }
    }
}
