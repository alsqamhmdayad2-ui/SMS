<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Enums\AttendanceStatus;

class AttendanceCalculationService
{
    /**
     * Calculate comprehensive attendance statistics for a student in a given context.
     *
     * Returns metrics ready for:
     * - Dashboard display
     * - Report card printing
     * - GradeCalculationService injection (via attendance_percentage)
     */
    public function calculate(int $studentId, int $academicYearId, ?int $semesterId = null, ?int $subjectId = null): array
    {
        $query = AttendanceRecord::where('student_id', $studentId)
            ->whereHas('session', function ($q) use ($academicYearId, $semesterId, $subjectId) {
                $q->where('academic_year_id', $academicYearId)
                  ->when($semesterId, fn($q2) => $q2->where('semester_id', $semesterId))
                  ->when($subjectId,  fn($q2) => $q2->where('subject_id', $subjectId));
            });

        $records = $query->get();

        $total    = $records->count();
        $present  = $records->where('status', AttendanceStatus::Present)->count();
        $absent   = $records->where('status', AttendanceStatus::Absent)->count();
        $late     = $records->where('status', AttendanceStatus::Late)->count();
        $excused  = $records->where('status', AttendanceStatus::Excused)->count();
        $sick     = $records->where('status', AttendanceStatus::Sick)->count();

        // Attendance rate: Present + Late (half) + Excused + Sick all count favourably
        // Strict calculation: only full Present counts as 100%, Late as 0.5
        $effectivePresent = $present + ($late * 0.5) + $excused + $sick;
        $attendancePercentage = $total > 0
            ? round(($effectivePresent / $total) * 100, 2)
            : 0;

        return [
            'total_sessions'        => $total,
            'present_count'         => $present,
            'absent_count'          => $absent,
            'late_count'            => $late,
            'excused_count'         => $excused,
            'sick_count'            => $sick,
            'attendance_percentage' => $attendancePercentage, // Ready for GradeCalculationService
        ];
    }

    /**
     * Calculate attendance stats for an entire section (for dashboard cards).
     */
    public function calculateForSection(int $sectionId, int $academicYearId, ?int $semesterId = null, ?string $date = null): array
    {
        $query = AttendanceRecord::whereHas('session', function ($q) use ($sectionId, $academicYearId, $semesterId, $date) {
            $q->where('section_id', $sectionId)
              ->where('academic_year_id', $academicYearId)
              ->when($semesterId, fn($q2) => $q2->where('semester_id', $semesterId))
              ->when($date,       fn($q2) => $q2->where('date', $date));
        });

        $records = $query->get();

        $total   = $records->count();
        $present = $records->where('status', AttendanceStatus::Present)->count();
        $absent  = $records->where('status', AttendanceStatus::Absent)->count();
        $late    = $records->where('status', AttendanceStatus::Late)->count();

        return [
            'total'              => $total,
            'present'            => $present,
            'absent'             => $absent,
            'late'               => $late,
            'present_percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'absent_percentage'  => $total > 0 ? round(($absent  / $total) * 100, 1) : 0,
            'late_percentage'    => $total > 0 ? round(($late    / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Return the top N most absent students in a section.
     */
    public function getTopAbsentees(int $sectionId, int $academicYearId, ?int $semesterId = null, int $limit = 5): array
    {
        $records = AttendanceRecord::with('student')
            ->where('status', AttendanceStatus::Absent)
            ->whereHas('session', function ($q) use ($sectionId, $academicYearId, $semesterId) {
                $q->where('section_id', $sectionId)
                  ->where('academic_year_id', $academicYearId)
                  ->when($semesterId, fn($q2) => $q2->where('semester_id', $semesterId));
            })
            ->get()
            ->groupBy('student_id')
            ->map(fn($group) => [
                'student'      => $group->first()->student,
                'absent_count' => $group->count(),
            ])
            ->sortByDesc('absent_count')
            ->take($limit)
            ->values()
            ->toArray();

        return $records;
    }
}
