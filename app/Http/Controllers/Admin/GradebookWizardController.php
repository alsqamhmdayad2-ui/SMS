<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentSemesterMark;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GradebookWizardController extends Controller
{
    use ApiResponse;

    /**
     * الخطوة 0: صفحة اختيار الصف (بالبطاقات)
     */
    public function index()
    {
        $currentAcademicYear = AcademicYear::where('status', true)->first();
        $currentSemester     = Semester::where('status', true)->first();

        $classes = SchoolClass::with(['grade', 'sections'])
            ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
            ->get();

        return view('panels.admin.gradebook-wizard.index', compact(
            'classes', 'currentAcademicYear', 'currentSemester'
        ));
    }

    /**
     * الخطوة 1: اختيار الشعبة لصف محدد
     */
    public function sections(SchoolClass $schoolClass)
    {
        $currentSemester = Semester::where('status', true)->first();

        $sections = $schoolClass->sections()->with('students')->get();

        return view('panels.admin.gradebook-wizard.sections', compact(
            'schoolClass', 'sections', 'currentSemester'
        ));
    }

    /**
     * الخطوة 2: اختيار المادة لشعبة محددة
     */
    public function subjects(Section $section)
    {
        $currentSemester = Semester::where('status', true)->first();
        $schoolClass = $section->schoolClass;

        // المواد المرتبطة بهذه الشعبة عبر subject_section_teacher
        $subjects = Subject::whereHas('sections', fn($q) => $q->where('sections.id', $section->id))
            ->get();

        // إذا لم توجد مواد على مستوى الشعبة، جرب على مستوى الصف
        if ($subjects->isEmpty()) {
            $subjects = $schoolClass->subjects ?? collect();
        }

        return view('panels.admin.gradebook-wizard.subjects', compact(
            'section', 'schoolClass', 'subjects', 'currentSemester'
        ));
    }

    /**
     * الخطوة 3: شاشة الرصد الشاملة (جدول الطلاب + 7 مكونات)
     */
    public function enter(Section $section, Subject $subject, Request $request)
    {
        $currentAcademicYear = AcademicYear::where('status', true)->first();
        $currentSemester     = Semester::where('status', true)->first();

        // يمكن تمرير semester_id في الـ query string
        $semesterId = $request->get('semester_id', $currentSemester?->id);
        $semester   = Semester::find($semesterId) ?? $currentSemester;

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

        // هل الفصل مقفل؟
        $isLocked = $marks->first()?->is_locked ?? false;

        // الفصول الدراسية للتبديل (إخفاء الفصول المستقبلية التي لم تبدأ بعد)
        $semesters = Semester::where('academic_year_id', $currentAcademicYear?->id)
            ->where(function($q) use ($currentSemester) {
                if ($currentSemester) {
                    $q->where('id', '<=', $currentSemester->id);
                }
            })
            ->get();

        // حساب درجة الحضور التلقائية (من 10) بناءً على سجل الغياب
        $totalSessions = \App\Models\AttendanceSession::where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->count();

        $autoAttendance = [];
        if ($totalSessions > 0) {
            $sessionIds = \App\Models\AttendanceSession::where('section_id', $section->id)
                ->where('subject_id', $subject->id)
                ->where('semester_id', $semester->id)
                ->pluck('id');

            $records = \App\Models\AttendanceRecord::whereIn('attendance_session_id', $sessionIds)
                ->whereIn('status', ['present', 'late'])
                ->selectRaw('student_id, count(*) as count')
                ->groupBy('student_id')
                ->pluck('count', 'student_id');

            foreach ($students as $student) {
                $attended = $records[$student->id] ?? 0;
                $calc = round(($attended / $totalSessions) * 10, 1);
                $autoAttendance[$student->id] = $calc;
            }
        }

        return view('panels.admin.gradebook-wizard.enter', compact(
            'section', 'subject', 'students', 'marks',
            'semester', 'semesters', 'isLocked', 'currentAcademicYear', 'autoAttendance'
        ));
    }

    /**
     * POST: حفظ جميع درجات الشعبة/المادة/الفصل دفعة واحدة
     */
    public function saveAll(Request $request): JsonResponse
    {
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

        // تحقق أن الفصل ليس مقفلاً
        $locked = StudentSemesterMark::where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            return $this->errorResponse('درجات هذا الفصل مقفلة ولا يمكن تعديلها.', 'LOCKED', [], 403);
        }

        DB::transaction(function () use ($request) {
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

                StudentSemesterMark::updateOrCreate(
                    [
                        'student_id'       => $markData['student_id'],
                        'subject_id'       => $request->subject_id,
                        'section_id'       => $request->section_id,
                        'semester_id'      => $request->semester_id,
                        'academic_year_id' => $request->academic_year_id,
                    ],
                    [
                        'activity'    => $markData['activity']   ?? null,
                        'attendance'  => $markData['attendance'] ?? null,
                        'homework'    => $markData['homework']   ?? null,
                        'monthly1'    => $markData['monthly1']   ?? null,
                        'midterm'     => $markData['midterm']    ?? null,
                        'monthly2'    => $markData['monthly2']   ?? null,
                        'final_exam'  => $markData['final_exam'] ?? null,
                        'total'       => $total,
                        'entered_by'  => auth()->id(),
                        'entered_at'  => now(),
                    ]
                );
            }
        });

        return $this->successResponse('تم حفظ الدرجات بنجاح!', null, 'MARKS_SAVED');
    }

    /**
     * POST: قفل/فتح درجات فصل كامل (للمدير فقط)
     */
    public function toggleLock(Request $request): JsonResponse
    {
        $request->validate([
            'section_id'       => 'required|exists:sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'semester_id'      => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'lock'             => 'required|boolean',
        ]);

        StudentSemesterMark::where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->update([
                'is_locked'  => $request->lock,
                'locked_by'  => $request->lock ? auth()->id() : null,
                'locked_at'  => $request->lock ? now() : null,
            ]);

        $msg = $request->lock ? 'تم قفل درجات الفصل بنجاح.' : 'تم فتح درجات الفصل.';
        return $this->successResponse($msg, null, 'LOCK_TOGGLED');
    }
}
