<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timetable;
use App\Models\AttendanceSession;
use App\Models\Teacher;
use App\Services\AttendanceService;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceSessionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function today(Request $request)
    {
        $teacher = Auth::user()?->teacher;

        if (!$teacher) {
            abort(403, 'No teacher profile linked to this account.');
        }

        $today = Carbon::today();

        $todayNames = array_values(array_unique([
            $today->format('l'),
            $today->format('D'),
            $today->translatedFormat('l'),
            $today->translatedFormat('D'),
        ]));

        // Get all timetable slots for this teacher today
        $teacherTimetables = Timetable::with(['section.schoolClass.grade', 'subject', 'academicYear', 'semester'])
            ->where('teacher_id', $teacher->id)
            ->where(function ($query) use ($todayNames) {
                foreach ($todayNames as $dayName) {
                    if (!empty($dayName)) {
                        $query->orWhere('day_of_week', $dayName);
                    }
                }
            })
            ->orderBy('period_number')
            ->get();

        // Group by section so we only show each section once
        $uniqueSections = collect();
        $processedSectionIds = [];

        foreach ($teacherTimetables as $tt) {
            if (in_array($tt->section_id, $processedSectionIds)) continue;

            $processedSectionIds[] = $tt->section_id;

            // Find the true first period for this section today (could be another teacher)
            $firstPeriod = Timetable::where('section_id', $tt->section_id)
                ->where(function ($query) use ($todayNames) {
                    foreach ($todayNames as $dayName) {
                        if (!empty($dayName)) {
                            $query->orWhere('day_of_week', $dayName);
                        }
                    }
                })
                ->orderBy('period_number')
                ->first();

            $isFirstPeriod = $firstPeriod && $firstPeriod->teacher_id === $teacher->id;

            $session = AttendanceSession::where('academic_year_id', $tt->academic_year_id)
                ->where('semester_id', $tt->semester_id)
                ->where('section_id', $tt->section_id)
                ->where('date', $today->toDateString())
                ->first();

            // We pass the timetable slot that gives them access (the first one they have)
            // But we mark whether they are the absolute first-period teacher for this section
            $tt->attendance_session  = $session;
            $tt->session_status      = $session?->status;
            $tt->is_first_period     = $isFirstPeriod;
            
            // If they are not first period, and session doesn't exist, we don't necessarily hide it,
            // we will let the view show "Attendance not taken" view-only.
            
            $uniqueSections->push($tt);
        }

        return view('panels.teacher.attendance.today', [
            'timetables' => $uniqueSections,
            'teacher'    => $teacher,
            'today'      => $today
        ]);
    }

    /**
     * Open (or auto-create) the attendance session for a timetable slot.
     */
    public function take(Request $request, Timetable $timetable)
    {
        $teacher = Auth::user()?->teacher;

        if (!$teacher || $timetable->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to take attendance for this class.');
        }

        $today = Carbon::today();
        $todayNames = array_values(array_unique([
            $today->format('l'),
            $today->format('D'),
            $today->translatedFormat('l'),
            $today->translatedFormat('D'),
        ]));

        $firstPeriod = Timetable::where('section_id', $timetable->section_id)
            ->where(function ($query) use ($todayNames) {
                foreach ($todayNames as $dayName) {
                    if (!empty($dayName)) {
                        $query->orWhere('day_of_week', $dayName);
                    }
                }
            })
            ->orderBy('period_number')
            ->first();

        $isFirstPeriod = $firstPeriod && $firstPeriod->id === $timetable->id;

        $sessionData = [
            'academic_year_id' => $timetable->academic_year_id,
            'semester_id'      => $timetable->semester_id,
            'section_id'       => $timetable->section_id,
            'subject_id'       => $timetable->subject_id,
            'teacher_id'       => $teacher->id,
            'timetable_id'     => $timetable->id,
            'date'             => $today->toDateString(),
            'period_number'    => $timetable->period_number,
        ];

        $session = $this->attendanceService->getSession($sessionData);

        if (!$session) {
            if (!$isFirstPeriod) {
                return redirect()->route('teacher.attendance.today')
                    ->with('error', 'تسجيل الحضور اليومي لهذه الشعبة مسموح فقط لمعلم الحصة الأولى. لم يقم معلم الحصة الأولى بتسجيل الحضور بعد.');
            }
            // Create it if it is the first period
            $session = $this->attendanceService->createSession($sessionData, Auth::id());
        }

        $records  = $session->records()->with('student')->get()->keyBy('student_id');
        $statuses = AttendanceStatus::cases();

        // If it is locked or not owned by the current teacher, they can only view.
        return view('panels.teacher.attendance.take', compact('session', 'timetable', 'records', 'statuses', 'today', 'isFirstPeriod'));
    }

    /**
     * Save/draft attendance (session stays OPEN).
     */
    public function save(Request $request, AttendanceSession $session)
    {
        $this->authorizeTeacher($session);

        $validated = $request->validate([
            'records'             => 'required|array',
            'records.*.student_id'=> 'required|exists:students,id',
            'records.*.status'    => 'required|in:present,absent,late,excused,sick',
            'records.*.remarks'   => 'nullable|string|max:255',
        ]);

        $this->attendanceService->updateAttendance($session, $validated['records'], Auth::id());
        $this->attendanceService->lockSession($session, Auth::id());

        return redirect()->route('teacher.attendance.today')
            ->with('success', 'تم حفظ واعتماد الحضور والغياب لهذه الشعبة اليوم بنجاح.');
    }

    /**
     * Lock the session — no further edits by teacher.
     */
    public function lock(Request $request, AttendanceSession $session)
    {
        $this->authorizeTeacher($session);

        $this->attendanceService->lockSession($session, Auth::id());

        return redirect()->route('teacher.attendance.today')
            ->with('success', 'تم إغلاق سجل الحضور بنجاح.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Ensure the authenticated teacher owns this session.
     */
    private function authorizeTeacher(AttendanceSession $session): void
    {
        $teacher = Auth::user()?->teacher;

        if (!$teacher || $session->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to modify this attendance session.');
        }

        if ($session->isLocked()) {
            abort(403, 'This attendance session is locked.');
        }
    }
}
