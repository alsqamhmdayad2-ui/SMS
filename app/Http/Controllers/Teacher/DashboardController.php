<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Enums\AttendanceStatus;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()?->teacher;
        $today   = Carbon::today();

        // ─── Today's timetable ───────────────────────────────────────────
        $todayNames = collect([
            $today->format('l'),
            $today->translatedFormat('l'),
            $today->format('D'),
            $today->translatedFormat('D'),
        ])->unique()->filter()->values();

        $todaySchedule = collect();
        if ($teacher) {
            $todaySchedule = Timetable::with(['section.schoolClass.grade', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where(function ($q) use ($todayNames) {
                    foreach ($todayNames as $day) {
                        if (!empty($day)) $q->orWhere('day_of_week', $day);
                    }
                })
                ->orderBy('period_number')
                ->get();
        }

        // ─── My distinct sections ─────────────────────────────────────────
        $mySections = collect();
        if ($teacher) {
            $mySections = Timetable::with(['section.schoolClass.grade', 'section.students'])
                ->where('teacher_id', $teacher->id)
                ->get()
                ->pluck('section')
                ->filter()
                ->unique('id')
                ->values();
        }

        // ─── Stats ────────────────────────────────────────────────────────
        $totalStudents = $mySections->sum(fn($s) => $s->students->count());

        // Absent count today (from attendance records marked today)
        $absentToday = 0;
        if ($teacher) {
            $todaySessions = AttendanceSession::where('teacher_id', $teacher->id)
                ->whereDate('date', $today)
                ->pluck('id');

            $absentToday = AttendanceRecord::whereIn('attendance_session_id', $todaySessions)
                ->where('status', AttendanceStatus::Absent)
                ->count();
        }

        return view('panels.teacher.dashboard', compact(
            'teacher',
            'today',
            'todaySchedule',
            'mySections',
            'totalStudents',
            'absentToday',
        ));
    }
}
