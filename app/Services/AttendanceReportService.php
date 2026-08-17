<?php

namespace App\Services;

use App\DTOs\AttendanceFilterData;
use App\Models\Student;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\AttendanceSession;
use App\Models\SchoolSetting;
use Carbon\Carbon;

/**
 * Responsible for assembling ready-to-render report payloads.
 * Delegates number crunching to AttendanceAnalyticsService.
 * Never calls PDF/Excel libraries directly.
 */
class AttendanceReportService
{
    public function __construct(
        protected AttendanceAnalyticsService $analytics
    ) {}

    /**
     * Student Attendance Report — full history + per-subject breakdown.
     */
    public function getStudentReport(int $studentId, AttendanceFilterData $filters): array
    {
        $student = Student::with(['section'])->findOrFail($studentId);
        $stats   = $this->analytics->getStudentStats($studentId, $filters);

        // Per-subject breakdown
        $subjectBreakdown = AttendanceSession::with('records', 'subject')
            ->where('section_id', $student->section_id)
            ->when($filters->academicYearId, fn($q) => $q->where('academic_year_id', $filters->academicYearId))
            ->when($filters->semesterId,     fn($q) => $q->where('semester_id',      $filters->semesterId))
            ->get()
            ->groupBy('subject_id')
            ->map(function ($sessions, $subjectId) use ($studentId) {
                $records = $sessions->flatMap(fn($s) => $s->records->where('student_id', $studentId));
                $total   = $records->count();
                $present = $records->where('status', 'present')->count();

                return [
                    'subject' => $sessions->first()->subject,
                    'total'   => $total,
                    'present' => $present,
                    'absent'  => $records->where('status', 'absent')->count(),
                    'late'    => $records->where('status', 'late')->count(),
                    'rate'    => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            })
            ->values();

        return [
            'student'           => $student,
            'stats'             => $stats,
            'subject_breakdown' => $subjectBreakdown,
            'school'            => SchoolSetting::first(),
            'generated_at'      => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Section Attendance Report — student ranking by attendance rate.
     */
    public function getSectionReport(int $sectionId, AttendanceFilterData $filters): array
    {
        $section   = Section::with(['schoolClass'])->findOrFail($sectionId);
        $students  = $this->analytics->getSectionStats($sectionId, $filters);
        $absentees = $this->analytics->getTopAbsentees($filters->with(sectionId: $sectionId));

        return [
            'section'    => $section,
            'students'   => $students,
            'absentees'  => $absentees,
            'school'     => SchoolSetting::first(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Teacher Attendance Report — session summary with per-class metrics.
     */
    public function getTeacherReport(int $teacherId, AttendanceFilterData $filters): array
    {
        $teacher = Teacher::findOrFail($teacherId);
        $stats   = $this->analytics->getTeacherStats($teacherId, $filters);

        return [
            'teacher'      => $teacher,
            'stats'        => $stats,
            'school'       => SchoolSetting::first(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Daily Summary Report — snapshot of today or any selected date.
     */
    public function getDailySummary(string $date, AttendanceFilterData $filters): array
    {
        $stats    = $this->analytics->getDailySummary($date, $filters->academicYearId);
        $sessions = AttendanceSession::with(['section.schoolClass', 'subject', 'teacher', 'records'])
            ->where('date', $date)
            ->when($filters->academicYearId, fn($q) => $q->where('academic_year_id', $filters->academicYearId))
            ->orderBy('period_number')
            ->get()
            ->map(function ($session) {
                $recs = $session->records;
                $total = $recs->count();
                $session->rate = $total > 0 ? round(($recs->where('status', 'present')->count() / $total) * 100) : 0;
                return $session;
            });

        return [
            'date'         => $date,
            'stats'        => $stats,
            'sessions'     => $sessions,
            'school'       => SchoolSetting::first(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Monthly trend — daily data points for charting or tabular view.
     */
    public function getMonthlySummary(string $month, AttendanceFilterData $filters): array
    {
        $dailyData  = $this->analytics->getMonthlySummary($month, $filters->sectionId);
        $absentees  = $this->analytics->getTopAbsentees($filters);
        $rankings   = $this->analytics->getSectionRankings($filters);

        return [
            'month'        => $month,
            'daily_data'   => $dailyData,
            'absentees'    => $absentees,
            'rankings'     => $rankings,
            'school'       => SchoolSetting::first(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }
}
