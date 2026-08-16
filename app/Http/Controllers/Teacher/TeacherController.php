<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Enums\AttendanceStatus;
use App\Models\Timetable;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    // ─── My Students ────────────────────────────────────────────────────
    public function students(Request $request)
    {
        $teacher = Auth::user()?->teacher;

        // Sections where this teacher teaches
        $mySectionIds = [];
        if ($teacher) {
            $mySectionIds = Timetable::where('teacher_id', $teacher->id)
                ->pluck('section_id')->unique()->values()->toArray();
        }

        $sectionsQuery = Section::with(['schoolClass.grade', 'students'])
            ->whereIn('id', $mySectionIds);

        $sections = $sectionsQuery->get();

        // Collect all students in those sections
        $students = $sections->flatMap(fn($s) => $s->students->map(function ($student) use ($s) {
            $student->setRelation('section', $s);
            return $student;
        }))->values();

        // Filter by section if requested
        if ($request->section_id) {
            $students = $students->filter(fn($s) => $s->section_id == $request->section_id)->values();
        }

        return view('panels.teacher.students', compact('students', 'sections', 'teacher'));
    }

    // ─── Schedule ────────────────────────────────────────────────────────
    public function schedule()
    {
        $teacher = Auth::user()?->teacher;

        $timetables = [];
        if ($teacher) {
            $timetables = Timetable::with(['section.schoolClass.grade', 'subject', 'academicYear', 'semester'])
                ->where('teacher_id', $teacher->id)
                ->orderByRaw("FIELD(day_of_week,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")
                ->orderBy('period_number')
                ->get()
                ->groupBy('day_of_week');
        }

        $dayNames = [
            'Sunday'    => 'الأحد',
            'Monday'    => 'الاثنين',
            'Tuesday'   => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday'  => 'الخميس',
            'Friday'    => 'الجمعة',
            'Saturday'  => 'السبت',
        ];

        return view('panels.teacher.schedule', compact('timetables', 'dayNames', 'teacher'));
    }

    // The 'grades' method has been replaced by GradebookController

    // ─── Profile ─────────────────────────────────────────────────────────
    public function profile()
    {
        $teacher = Auth::user()?->teacher;
        $user    = Auth::user();

        $subjects = $teacher?->subjects ?? collect();
        $sections = [];
        if ($teacher) {
            $sections = Timetable::with(['section.schoolClass.grade'])
                ->where('teacher_id', $teacher->id)
                ->get()
                ->pluck('section')->filter()->unique('id')->values();
        }

        return view('panels.teacher.profile', compact('teacher', 'user', 'subjects', 'sections'));
    }

    // ─── Update Profile ──────────────────────────────────────────────────
    public function updateProfile(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        $request->validate([
            'email'   => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Update email if provided
        if ($request->filled('email')) {
            $user->update(['email' => $request->email]);
        }

        // Update Teacher info
        if ($teacher) {
            $data = $request->only(['phone', 'address']);

            if ($request->hasFile('avatar')) {
                // Delete old avatar if it exists
                if ($teacher->avatar) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->avatar);
                }
                $path = $request->file('avatar')->store('avatars/teachers', 'public');
                $data['avatar'] = $path;
            }

            $teacher->update($data);
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }
}
