<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Timetable;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentSemesterMark;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    use ApiResponse;

    /**
     * لوحة المعلم - عرض جميع مواده وشعبه كبطاقات
     */
    public function index()
    {
        $teacher = Auth::user()?->teacher;
        if (!$teacher) {
            abort(403, 'لم يتم ربط حسابك بملف معلم.');
        }

        $currentAcademicYear = AcademicYear::where('status', true)->first();
        $currentSemester     = Semester::where('status', true)->first();

        // جلب جميع الحصص الخاصة بهذا المعلم (مع التقليل من التكرار)
        $assignments = Timetable::with(['section.schoolClass.grade', 'subject', 'semester'])
            ->where('teacher_id', $teacher->id)
            ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
            ->get()
            ->unique(fn($t) => $t->section_id . '-' . $t->subject_id . '-' . $t->semester_id);

        // تجميع البطاقات حسب (section + subject + semester)
        $cards = $assignments->map(function ($timetable) use ($currentAcademicYear) {
            $section  = $timetable->section;
            $subject  = $timetable->subject;
            $semester = $timetable->semester;

            // عدد الطلاب في الشعبة
            $studentCount = $section?->students()->count() ?? 0;

            // هل هناك درجات مدخلة؟
            $enteredCount = StudentSemesterMark::where('section_id', $section?->id)
                ->where('subject_id', $subject?->id)
                ->where('semester_id', $semester?->id)
                ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
                ->count();

            $isLocked = StudentSemesterMark::where('section_id', $section?->id)
                ->where('subject_id', $subject?->id)
                ->where('semester_id', $semester?->id)
                ->where('is_locked', true)
                ->exists();

            return [
                'timetable_id' => $timetable->id,
                'section'      => $section,
                'subject'      => $subject,
                'semester'     => $semester,
                'student_count' => $studentCount,
                'entered_count' => $enteredCount,
                'is_locked'    => $isLocked,
                'completion'   => $studentCount > 0 ? round(($enteredCount / $studentCount) * 100) : 0,
            ];
        })->values();

        return view('panels.teacher.gradebook.index', compact(
            'teacher', 'cards', 'currentAcademicYear', 'currentSemester'
        ));
    }

    /**
     * شاشة رصد الدرجات لشعبة + مادة + فصل محدد
     */
    public function enter(Request $request, Section $section, Subject $subject)
    {
        $teacher = Auth::user()?->teacher;
        if (!$teacher) {
            abort(403, 'لم يتم ربط حسابك بملف معلم.');
        }

        $currentAcademicYear = AcademicYear::where('status', true)->first();
        $currentSemester     = Semester::where('status', true)->first();

        $semesterId = $request->get('semester_id', $currentSemester?->id);
        $semester   = Semester::find($semesterId) ?? $currentSemester;

        // التحقق من أن المعلم يدرس هذه المادة في هذه الشعبة
        $hasAccess = Timetable::where('teacher_id', $teacher->id)
            ->where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'ليس لديك صلاحية لرصد هذه المادة في هذه الشعبة.');
        }

        $students = Student::where('section_id', $section->id)
            ->orderBy('first_name')
            ->get();

        // جلب الدرجات الموجودة
        $marks = StudentSemesterMark::where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semesterId)
            ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
            ->get()
            ->keyBy('student_id');

        $isLocked = $marks->first()?->is_locked ?? false;

        // إذا كان الفصل المختار غير نشط (مثلاً فصل سابق انتهى)، نمنع التعديل عليه للمعلم
        if (!$semester->status) {
            $isLocked = true;
        }

        // حساب درجة الحضور التلقائية (من 10) بناءً على سجل الغياب
        $totalSessions = \App\Models\AttendanceSession::where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semesterId)
            ->count();

        $autoAttendance = [];
        if ($totalSessions > 0) {
            $sessionIds = \App\Models\AttendanceSession::where('section_id', $section->id)
                ->where('subject_id', $subject->id)
                ->where('semester_id', $semesterId)
                ->pluck('id');

            $records = \App\Models\AttendanceRecord::whereIn('attendance_session_id', $sessionIds)
                ->whereIn('status', ['present', 'late']) // Assuming string values from Enum
                ->selectRaw('student_id, count(*) as count')
                ->groupBy('student_id')
                ->pluck('count', 'student_id');

            foreach ($students as $student) {
                $attended = $records[$student->id] ?? 0;
                // حساب النسبة من 10 (يسمح بأنصاف الدرجات)
                $calc = round(($attended / $totalSessions) * 10, 1);
                $autoAttendance[$student->id] = $calc;
            }
        }

        // إخفاء الفصول المستقبلية التي لم تبدأ بعد (بناءً على الترتيب أو الحالة)
        // نجلب الفصول التي إما نشطة، أو ترتيبها يسبق/يساوي الفصل النشط
        $semesters = Semester::when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
            ->where(function($q) use ($currentSemester) {
                if ($currentSemester) {
                    $q->where('id', '<=', $currentSemester->id);
                }
            })
            ->get();

        return view('panels.teacher.gradebook.enter', compact(
            'teacher', 'section', 'subject', 'students', 'marks',
            'semester', 'semesters', 'isLocked', 'currentAcademicYear', 'autoAttendance'
        ));
    }

    /**
     * حفظ الدرجات (POST) - للمعلم فقط، بدون حق القفل
     */
    public function saveAll(Request $request): JsonResponse
    {
        $teacher = Auth::user()?->teacher;
        if (!$teacher) {
            return $this->errorResponse('غير مصرح لك.', 'UNAUTHORIZED', [], 403);
        }

        $request->validate([
            'section_id'       => 'required|exists:sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'semester_id'      => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'marks'            => 'required|array',
            'marks.*.student_id'  => 'required|exists:students,id',
            'marks.*.activity'    => 'nullable|numeric|min:0|max:10',
            'marks.*.attendance'  => 'nullable|numeric|min:0|max:10',
            'marks.*.homework'    => 'nullable|numeric|min:0|max:10',
            'marks.*.monthly1'    => 'nullable|numeric|min:0|max:10',
            'marks.*.midterm'     => 'nullable|numeric|min:0|max:20',
            'marks.*.monthly2'    => 'nullable|numeric|min:0|max:10',
            'marks.*.final_exam'  => 'nullable|numeric|min:0|max:30',
        ]);

        // التحقق من أن المعلم يدرس هذه المادة في هذه الشعبة
        $hasAccess = Timetable::where('teacher_id', $teacher->id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$hasAccess) {
            return $this->errorResponse('ليس لديك صلاحية لرصد هذه المادة.', 'FORBIDDEN', [], 403);
        }

        // التحقق من أن الدرجات غير مقفولة من الإدارة
        $locked = StudentSemesterMark::where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            return $this->errorResponse('درجات هذا الفصل مقفلة من قبل الإدارة ولا يمكن تعديلها.', 'LOCKED', [], 403);
        }

        DB::transaction(function () use ($request, $teacher) {
            foreach ($request->marks as $markData) {
                $total = array_sum(array_filter([
                    $markData['activity']   ?? 0,
                    $markData['attendance'] ?? 0,
                    $markData['homework']   ?? 0,
                    $markData['monthly1']   ?? 0,
                    $markData['midterm']    ?? 0,
                    $markData['monthly2']   ?? 0,
                    $markData['final_exam'] ?? 0,
                ], 'is_numeric'));

                // تحديد التقدير
                $letterGrade = match(true) {
                    $total >= 90 => 'ممتاز',
                    $total >= 80 => 'جيد جداً',
                    $total >= 70 => 'جيد',
                    $total >= 60 => 'مقبول',
                    $total >= 50 => 'ضعيف',
                    default      => 'راسب',
                };

                StudentSemesterMark::updateOrCreate(
                    [
                        'student_id'       => $markData['student_id'],
                        'subject_id'       => $request->subject_id,
                        'section_id'       => $request->section_id,
                        'semester_id'      => $request->semester_id,
                        'academic_year_id' => $request->academic_year_id,
                    ],
                    [
                        'activity'     => $markData['activity']   ?? null,
                        'attendance'   => $markData['attendance'] ?? null,
                        'homework'     => $markData['homework']   ?? null,
                        'monthly1'     => $markData['monthly1']   ?? null,
                        'midterm'      => $markData['midterm']    ?? null,
                        'monthly2'     => $markData['monthly2']   ?? null,
                        'final_exam'   => $markData['final_exam'] ?? null,
                        'total'        => $total,
                        'letter_grade' => $letterGrade,
                        'entered_by'   => auth()->id(),
                        'entered_at'   => now(),
                    ]
                );
            }
        });

        return $this->successResponse('تم حفظ الدرجات بنجاح!', null, 'MARKS_SAVED');
    }
}
