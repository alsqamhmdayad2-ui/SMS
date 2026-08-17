<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\Subject;
use App\Services\AttendanceService;
use App\Services\AttendanceCalculationService;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceSessionStatus;

class AttendanceAdminController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AttendanceCalculationService $calculationService
    ) {}

    /**
     * Session list with filters.
     */
    public function index(Request $request)
    {
        $sessions = AttendanceSession::with(['subject', 'teacher', 'records', 'section.schoolClass'])
            ->when($request->academic_year_id, fn($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->semester_id,      fn($q) => $q->where('semester_id', $request->semester_id))
            ->when($request->section_id,       fn($q) => $q->where('section_id', $request->section_id))

            ->when($request->status,           fn($q) => $q->where('status', $request->status))
            ->when($request->date,             fn($q) => $q->where('date', $request->date))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        // Attach quick attendance rate to each session
        $sessions->through(function ($session) {
            $records = $session->records;
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $session->attendance_rate = $total > 0 ? round(($present / $total) * 100) : 0;
            return $session;
        });

        $filters = [
            'academicYears' => AcademicYear::all(),
            'semesters'     => Semester::all(),
            'sections'      => Section::with('schoolClass.grade')->get(),
        ];

        return view('panels.admin.attendance.index', compact('sessions', 'filters'));
    }

    /**
     * Session detail view with student records and audit timeline.
     */
    public function show(AttendanceSession $session)
    {
        $session->load([
            'section.schoolClass',
            'subject',
            'teacher',
            'records.student',
            'records.overrides.overriddenBy',
            'records.markedBy',
            'lockedBy',
        ]);

        $stats    = $this->calculationService->calculateForSection(
            $session->section_id,
            $session->academic_year_id,
            $session->semester_id,
            $session->date->toDateString()
        );
        $statuses = AttendanceStatus::cases();

        return view('panels.admin.attendance.show', compact('session', 'stats', 'statuses'));
    }

    /**
     * Admin override for a single student record.
     */
    public function override(Request $request, AttendanceSession $session)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'status'     => 'required|in:present,absent,late,excused,sick',
            'reason'     => 'required|string|min:5|max:500',
        ]);

        $this->attendanceService->adminOverride(
            $session,
            (int) $validated['student_id'],
            $validated['status'],
            $validated['reason'],
            auth()->id()
        );

        return back()->with('success', 'Attendance record overridden and audit log updated.');
    }

    /**
     * Unlock a locked session (admin only, with reason).
     */
    public function unlock(Request $request, AttendanceSession $session)
    {
        $request->validate([
            'unlock_reason' => 'required|string|min:5|max:500',
        ]);

        $this->attendanceService->unlockSession($session);

        return back()->with('success', 'Session unlocked successfully.');
    }

    /**
     * Lock a session from admin side.
     */
    public function lock(AttendanceSession $session)
    {
        try {
            $this->attendanceService->lockSession($session, auth()->id());
            return back()->with('success', 'Session locked successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Show form to manually create an attendance session (Admin Override).
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        $semesters     = Semester::all();
        $sections      = Section::with('schoolClass.grade')->get();

        return view('panels.admin.attendance.create', compact('academicYears', 'semesters', 'sections'));
    }

    /**
     * Manually create an attendance session if it does not exist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
            'section_id'       => 'required|exists:sections,id',
            'date'             => 'required|date|before_or_equal:today',
        ]);

        $sessionData = [
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id'      => $validated['semester_id'],
            'section_id'       => $validated['section_id'],
            'date'             => $validated['date'],
            'subject_id'       => null,
            'teacher_id'       => $this->getAdminTeacherId(), // Safe: only set if admin is also a teacher
            'timetable_id'     => null,
            'period_number'    => 1,
        ];

        // Check for soft-deleted duplicate first, restore it
        $trashed = \App\Models\AttendanceSession::onlyTrashed()
            ->where('academic_year_id', $sessionData['academic_year_id'])
            ->where('semester_id', $sessionData['semester_id'])
            ->where('section_id', $sessionData['section_id'])
            ->whereDate('date', $sessionData['date'])
            ->first();

        if ($trashed) {
            $trashed->restore();
            return redirect()->route('admin.attendance-sessions.show', $trashed->id)
                ->with('info', 'تم استعادة جلسة الحضور السابقة.');
        }

        // Check for existing active session
        $session = $this->attendanceService->getSession($sessionData);

        if ($session) {
            return redirect()->route('admin.attendance-sessions.show', $session->id)
                ->with('info', 'جلسة الحضور موجودة مسبقاً. يمكنك تعديلها من هنا.');
        }

        $session = $this->attendanceService->createSession($sessionData, auth()->id());

        return redirect()->route('admin.attendance-sessions.show', $session->id)
            ->with('success', 'تم فتح جلسة حضور جديدة لهذه الشعبة. يمكنك الآن تعديل حالة الغائبين.');
    }

    /**
     * Returns the teacher ID if the logged-in admin also has a teacher record.
     * Returns null otherwise (admin-only action).
     */
    private function getAdminTeacherId(): ?int
    {
        return \App\Models\Teacher::where('user_id', auth()->id())->value('id');
    }
}
