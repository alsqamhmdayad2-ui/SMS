<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\Timetable;
use App\Enums\ExamStatus;
use App\Services\ExamResultService;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    use ApiResponse;

    public function __construct(protected ExamResultService $examResultService) {}

    // ─── Helper: get teacher & their section/subject assignments ──────────────
    protected function getTeacher()
    {
        return Auth::user()?->teacher;
    }

    protected function getTeacherSectionIds($teacher): array
    {
        if (!$teacher) return [];
        return Timetable::where('teacher_id', $teacher->id)
            ->pluck('section_id')->unique()->values()->toArray();
    }

    protected function getTeacherSubjectIds($teacher): array
    {
        if (!$teacher) return [];
        return Timetable::where('teacher_id', $teacher->id)
            ->pluck('subject_id')->unique()->values()->toArray();
    }

    // ─── My Exams Index ───────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        $query = Exam::with(['section.schoolClass', 'subject', 'academicYear', 'semester'])
            ->whereIn('section_id', $sectionIds)
            ->whereIn('subject_id', $subjectIds);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->latest()->paginate(15)->withQueryString();

        $sections = Section::with('schoolClass.grade')->whereIn('id', $sectionIds)->get();
        $subjects = Subject::whereIn('id', $subjectIds)->get();
        $statuses = ExamStatus::cases();

        return view('panels.teacher.exams.index', compact(
            'exams', 'sections', 'subjects', 'statuses', 'teacher'
        ));
    }

    // ─── Create Exam ─────────────────────────────────────────────────────────
    public function create()
    {
        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        $sections     = Section::with('schoolClass.grade')->whereIn('id', $sectionIds)->get();
        $subjects     = Subject::whereIn('id', $subjectIds)->get();
        $academicYears = AcademicYear::where('status', true)->get();
        $semesters    = Semester::where('status', true)->get();
        $statuses     = ExamStatus::cases();

        return view('panels.teacher.exams.create', compact(
            'sections', 'subjects', 'academicYears', 'semesters', 'statuses', 'teacher'
        ));
    }

    // ─── Store Exam ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|in:quiz,midterm,final,assignment',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
            'section_id'       => 'required|in:' . implode(',', $sectionIds),
            'subject_id'       => 'required|in:' . implode(',', $subjectIds),
            'exam_date'        => 'nullable|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:5',
            'display_mode'     => 'required|in:single_page,per_question',
            'instructions'     => 'nullable|string',
        ]);

        // Resolve grade_id from section
        $section = Section::with('schoolClass')->findOrFail($validated['section_id']);
        $validated['grade_id'] = $section->schoolClass?->grade_id;
        $validated['class_id'] = $section->class_id;
        $validated['teacher_id'] = $teacher?->id;
        $validated['status'] = ExamStatus::DRAFT->value;

        $exam = Exam::create($validated);

        return redirect()->route('teacher.exams.show', $exam->id)
            ->with('success', 'تم إنشاء الاختبار بنجاح. يمكنك الآن إدخال درجات الطلاب.');
    }

    // ─── Show Exam (Marks Entry) ──────────────────────────────────────────────
    public function show(Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        $exam->load(['section.schoolClass.grade', 'subject', 'academicYear', 'semester']);

        $students = Student::where('section_id', $exam->section_id)
            ->orderBy('first_name')->get();

        $results = ExamResult::where('exam_id', $exam->id)
            ->get()->keyBy('student_id');

        return view('panels.teacher.exams.show', compact(
            'exam', 'students', 'results'
        ));
    }

    // ─── Edit Exam ────────────────────────────────────────────────────────────
    public function edit(Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        $sections     = Section::with('schoolClass.grade')->whereIn('id', $sectionIds)->get();
        $subjects     = Subject::whereIn('id', $subjectIds)->get();
        $academicYears = AcademicYear::where('status', true)->get();
        $semesters    = Semester::where('status', true)->get();

        return view('panels.teacher.exams.edit', compact(
            'exam', 'sections', 'subjects', 'academicYears', 'semesters'
        ));
    }

    // ─── Update Exam ──────────────────────────────────────────────────────────
    public function update(Request $request, Exam $exam)
    {
        $this->authorizeTeacherExam($exam);
        if ($exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'لا يمكن تعديل اختبار منشور أو مغلق.');
        }

        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|in:quiz,midterm,final,assignment',
            'exam_date'        => 'nullable|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:5',
            'display_mode'     => 'required|in:single_page,per_question',
            'instructions'     => 'nullable|string',
        ]);

        $exam->update($validated);

        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم تحديث الاختبار بنجاح.');
    }

    // ─── Delete Exam ──────────────────────────────────────────────────────────
    public function destroy(Exam $exam)
    {
        $this->authorizeTeacherExam($exam);
        if ($exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'لا يمكن حذف اختبار منشور.');
        }
        $exam->delete();
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم حذف الاختبار.');
    }

    // ─── Publish Exam ─────────────────────────────────────────────────────────
    public function publish(Exam $exam)
    {
        $this->authorizeTeacherExam($exam);
        
        if ($exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'الاختبار ليس في حالة المسودة.');
        }

        if ($exam->questions()->count() === 0) {
            // Uncomment next line if you require questions to publish
            // return back()->with('error', 'لا يمكن نشر اختبار بدون أسئلة.');
        }

        $exam->update(['status' => ExamStatus::PUBLISHED->value]);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'تم نشر الاختبار بنجاح.');
    }

    // ─── AJAX: Save single mark ───────────────────────────────────────────────
    public function saveMark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id'           => 'required|exists:exams,id',
            'student_id'        => 'required|exists:students,id',
            'marks_obtained'    => 'nullable|numeric|min:0',
            'attendance_status' => 'required|in:present,absent,excused,cheating,incomplete',
            'remarks'           => 'nullable|string|max:500',
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $this->authorizeTeacherExam($exam);

        if ($validated['marks_obtained'] !== null && $validated['marks_obtained'] > $exam->total_marks) {
            return $this->errorResponse('الدرجة تتجاوز الدرجة الكلية (' . $exam->total_marks . ')', 'VALIDATION_FAILED', [], 422);
        }

        try {
            $result   = $this->examResultService->saveSingleMark($exam, $validated, auth()->id());
            $gradeInfo = $this->examResultService->resolveGrade($result->percentage);

            return $this->successResponse('تم الحفظ', [
                'marks_obtained'    => $result->marks_obtained,
                'percentage'        => $result->percentage,
                'attendance_status' => $result->attendance_status,
                'letter_grade'      => $gradeInfo['letter_grade'] ?? null,
                'is_passing'        => $gradeInfo['is_passing'] ?? null,
            ], 'MARK_SAVED');
        } catch (\Exception $e) {
            return $this->errorResponse('خطأ: ' . $e->getMessage(), 'ERROR', [], 500);
        }
    }

    // ─── AJAX: Bulk save all marks ────────────────────────────────────────────
    public function saveAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id'                       => 'required|exists:exams,id',
            'results'                       => 'required|array',
            'results.*.student_id'          => 'required|exists:students,id',
            'results.*.marks_obtained'      => 'nullable|numeric|min:0',
            'results.*.attendance_status'   => 'required|in:present,absent,excused,cheating,incomplete',
            'results.*.remarks'             => 'nullable|string|max:500',
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $this->authorizeTeacherExam($exam);

        try {
            $results = $this->examResultService->bulkSaveMarks($exam, $validated['results'], auth()->id());
            return $this->successResponse(count($results) . ' نتيجة تم حفظها بنجاح.', null, 'MARKS_BULK_SAVED');
        } catch (\Exception $e) {
            return $this->errorResponse('فشل الحفظ: ' . $e->getMessage(), 'ERROR', [], 500);
        }
    }

    // ─── Authorization Helper ─────────────────────────────────────────────────
    protected function authorizeTeacherExam(Exam $exam): void
    {
        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        if (!in_array($exam->section_id, $sectionIds) || !in_array($exam->subject_id, $subjectIds)) {
            abort(403, 'غير مصرح لك بالوصول لهذا الاختبار.');
        }
    }

    // ─── Review and Grade Student Answers ──────────────────────────────────────
    public function reviewAnswers(Exam $exam, Student $student)
    {
        $this->authorizeTeacherExam($exam);

        $examResult = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $answers = \Illuminate\Support\Facades\DB::table('exam_student_answers')
            ->where('exam_result_id', $examResult->id)
            ->get()
            ->keyBy('question_id');

        $exam->load('questions.options');

        return view('panels.teacher.exams.review', compact('exam', 'student', 'examResult', 'answers'));
    }

    public function saveGrades(\Illuminate\Http\Request $request, Exam $exam, Student $student)
    {
        $this->authorizeTeacherExam($exam);

        $examResult = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $grades = $request->input('grades', []);
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($examResult, $grades) {
            foreach ($grades as $questionId => $mark) {
                \Illuminate\Support\Facades\DB::table('exam_student_answers')
                    ->where('exam_result_id', $examResult->id)
                    ->where('question_id', $questionId)
                    ->update([
                        'marks_awarded' => $mark,
                        'is_graded' => true,
                    ]);
            }
            
            // Recalculate total marks
            $totalMarks = \Illuminate\Support\Facades\DB::table('exam_student_answers')
                ->where('exam_result_id', $examResult->id)
                ->sum('marks_awarded');
                
            $examResult->update(['marks_obtained' => $totalMarks]);
        });

        return redirect()->route('teacher.exams.show', $exam)->with('success', 'تم حفظ الدرجات وتحديث المجموع بنجاح.');
    }
}
