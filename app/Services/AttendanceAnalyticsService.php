<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Enums\AttendanceStatus;
use App\DTOs\AttendanceFilterData;
use Carbon\Carbon;

/**
 * Responsible ONLY for statistical aggregation and analytics.
 * AttendanceService handles CRUD; this handles numbers.
 */
class AttendanceAnalyticsService
{
    /**
     * Daily summary across all sections (for Dashboard Today card).
     */
    public function getDailySummary(string $date, ?int $academicYearId = null): array
    {
        $records = AttendanceRecord::whereHas('session', function ($q) use ($date, $academicYearId) {
            $q->where('date', $date)
              ->when($academicYearId, fn($q2) => $q2->where('academic_year_id', $academicYearId));
        })->get();

        return $this->aggregateRecords($records);
    }

    /**
     * Weekly summary (last 7 days).
     */
    public function getWeeklySummary(?int $academicYearId = null): array
    {
        $from = Carbon::today()->subDays(6)->toDateString();
        $to   = Carbon::today()->toDateString();

        $records = AttendanceRecord::whereHas('session', function ($q) use ($from, $to, $academicYearId) {
            $q->whereBetween('date', [$from, $to])
              ->when($academicYearId, fn($q2) => $q2->where('academic_year_id', $academicYearId));
        })->get();

        return $this->aggregateRecords($records);
    }

    /**
     * Monthly breakdown: one data point per day within a given month (Y-m format).
     */
    public function getMonthlySummary(string $month, ?int $sectionId = null): array
    {
        $from = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $to   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $sessions = AttendanceSession::with('records')
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $dailyData = [];

        foreach ($sessions as $session) {
            $day = $session->date->toDateString();
            if (!isset($dailyData[$day])) {
                $dailyData[$day] = ['date' => $day, 'present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0];
            }
            foreach ($session->records as $rec) {
                $dailyData[$day]['total']++;
                $status = $rec->status->value;
                if (isset($dailyData[$day][$status])) {
                    $dailyData[$day][$status]++;
                }
            }
        }

        return array_values($dailyData);
    }

    /**
     * Student-level statistics across all subjects or filtered by subject.
     */
    public function getStudentStats(int $studentId, AttendanceFilterData $filters): array
    {
        $records = AttendanceRecord::where('student_id', $studentId)
            ->whereHas('session', function ($q) use ($filters) {
                $q->when($filters->academicYearId, fn($q2) => $q2->where('academic_year_id', $filters->academicYearId))
                  ->when($filters->semesterId,     fn($q2) => $q2->where('semester_id',      $filters->semesterId))
                  ->when($filters->subjectId,      fn($q2) => $q2->where('subject_id',       $filters->subjectId))
                  ->when($filters->dateFrom,       fn($q2) => $q2->where('date', '>=', $filters->dateFrom))
                  ->when($filters->dateTo,         fn($q2) => $q2->where('date', '<=', $filters->dateTo));
            })
            ->get();

        return $this->aggregateRecords($records);
    }

    /**
     * Section-level statistics: one row per student showing their attendance summary.
     */
    public function getSectionStats(int $sectionId, AttendanceFilterData $filters): array
    {
        $sessionIds = AttendanceSession::where('section_id', $sectionId)
            ->when($filters->academicYearId, fn($q) => $q->where('academic_year_id', $filters->academicYearId))
            ->when($filters->semesterId,     fn($q) => $q->where('semester_id',      $filters->semesterId))
            ->pluck('id');

        $records = AttendanceRecord::with('student')
            ->whereIn('attendance_session_id', $sessionIds)
            ->get()
            ->groupBy('student_id');

        $stats = [];
        foreach ($records as $studentId => $studentRecords) {
            $agg = $this->aggregateRecords($studentRecords);
            $stats[] = [
                'student' => $studentRecords->first()->student,
                ...$agg,
            ];
        }

        // Sort by attendance rate descending
        usort($stats, fn($a, $b) => $b['attendance_percentage'] <=> $a['attendance_percentage']);

        return $stats;
    }

    /**
     * Teacher-level statistics: summary of all sessions taken by a teacher.
     */
    public function getTeacherStats(int $teacherId, AttendanceFilterData $filters): array
    {
        $sessions = AttendanceSession::with(['records', 'subject', 'section.schoolClass'])
            ->where('teacher_id', $teacherId)
            ->when($filters->academicYearId, fn($q) => $q->where('academic_year_id', $filters->academicYearId))
            ->when($filters->semesterId,     fn($q) => $q->where('semester_id',      $filters->semesterId))
            ->orderByDesc('date')
            ->get();

        $totalSessions = $sessions->count();
        $lockedSessions = $sessions->where('status.value', 'locked')->count();
        $allRecords = $sessions->flatMap(fn($s) => $s->records);

        return [
            'total_sessions'  => $totalSessions,
            'locked_sessions' => $lockedSessions,
            'open_sessions'   => $totalSessions - $lockedSessions,
            'total_students'  => $allRecords->unique('student_id')->count(),
            ...$this->aggregateRecords($allRecords),
            'sessions'        => $sessions,
        ];
    }

    /**
     * Top absentees across school (or section).
     */
    public function getTopAbsentees(AttendanceFilterData $filters, int $limit = 10): array
    {
        return AttendanceRecord::with('student.section.schoolClass')
            ->where('status', AttendanceStatus::Absent->value)
            ->whereHas('session', function ($q) use ($filters) {
                $q->when($filters->academicYearId, fn($q2) => $q2->where('academic_year_id', $filters->academicYearId))
                  ->when($filters->semesterId,     fn($q2) => $q2->where('semester_id',      $filters->semesterId))
                  ->when($filters->sectionId,      fn($q2) => $q2->where('section_id',       $filters->sectionId));
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
    }

    /**
     * Sections ranked by average attendance rate (lowest first = needs attention).
     */
    public function getSectionRankings(AttendanceFilterData $filters): array
    {
        $sessions = AttendanceSession::with(['records', 'section.schoolClass'])
            ->when($filters->academicYearId, fn($q) => $q->where('academic_year_id', $filters->academicYearId))
            ->when($filters->semesterId,     fn($q) => $q->where('semester_id',      $filters->semesterId))
            ->get()
            ->groupBy('section_id');

        $rankings = [];
        foreach ($sessions as $sectionId => $sectionSessions) {
            $allRecords = $sectionSessions->flatMap(fn($s) => $s->records);
            $total   = $allRecords->count();
            $present = $allRecords->where('status', 'present')->count();
            $rate    = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $rankings[] = [
                'section_id'   => $sectionId,
                'section'      => $sectionSessions->first()->section ?? null,
                'total'        => $total,
                'present'      => $present,
                'rate'         => $rate,
            ];
        }

        usort($rankings, fn($a, $b) => $a['rate'] <=> $b['rate']); // Lowest first

        return $rankings;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function aggregateRecords($records): array
    {
        $total   = $records->count();
        $present = $records->where('status', AttendanceStatus::Present->value)->count();
        $absent  = $records->where('status', AttendanceStatus::Absent->value)->count();
        $late    = $records->where('status', AttendanceStatus::Late->value)->count();
        $excused = $records->where('status', AttendanceStatus::Excused->value)->count();

        $effective  = $present + ($late * 0.5) + $excused;
        $rate       = $total > 0 ? round(($effective / $total) * 100, 1) : 0;

        return [
            'total_sessions'        => $total,
            'present_count'         => $present,
            'absent_count'          => $absent,
            'late_count'            => $late,
            'excused_count'         => $excused,
            'sick_count'            => 0, // Kept for backward compatibility if used in views
            'attendance_percentage' => $rate, // Ready for GradeCalculationService
            'present_percentage'    => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'absent_percentage'     => $total > 0 ? round(($absent  / $total) * 100, 1) : 0,
        ];
    }
}
