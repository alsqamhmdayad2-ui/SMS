<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Services\ExamResultService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Http\ApiResponse;

class MarksEntryController extends Controller
{
    use ApiResponse;

    protected ExamResultService $examResultService;

    public function __construct(ExamResultService $examResultService)
    {
        $this->examResultService = $examResultService;
    }

    /**
     * Main marks entry page with filters.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $grades = Grade::all();
        $subjects = Subject::all();

        // Detect current academic year & semester
        $currentAcademicYear = AcademicYear::where('status', true)->first();
        $currentSemester = Semester::where('status', true)->first();

        $classes = SchoolClass::all();

        // Get sections based on selected filters
        $sections = collect();
        if ($request->filled('class_id')) {
            $sections = Section::where('class_id', $request->class_id)->with('schoolClass')->get();
        }

        // Get exams based on selected filters
        $exams = collect();
        if ($request->filled(['academic_year_id', 'section_id', 'subject_id'])) {
            $exams = Exam::where('academic_year_id', $request->academic_year_id)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)
                ->when($request->filled('semester_id'), function ($q) use ($request) {
                    $q->where('semester_id', $request->semester_id);
                })
                ->get();
        }

        // Load students and results when exam is selected
        $students = collect();
        $exam = null;
        $results = collect();
        if ($request->filled('exam_id')) {
            $exam = Exam::with(['subject', 'section', 'academicYear', 'semester'])->findOrFail($request->exam_id);

            $students = Student::where('section_id', $exam->section_id)
                ->orderBy('first_name')
                ->get();

            $results = ExamResult::where('exam_id', $exam->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('panels.admin.exams.marks-entry.index', compact(
            'academicYears', 'semesters', 'grades', 'classes', 'subjects',
            'sections', 'exams', 'students', 'exam', 'results',
            'currentAcademicYear', 'currentSemester'
        ));
    }

    /**
     * AJAX: Save a single student's mark (auto-save on blur).
     */
    public function saveMark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'marks_obtained' => 'nullable|numeric|min:0',
            'attendance_status' => 'required|in:present,absent,excused,cheating,incomplete',
            'remarks' => 'nullable|string|max:500',
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);

        // Validate marks don't exceed total
        if ($validated['marks_obtained'] !== null && $validated['marks_obtained'] > $exam->total_marks) {
            return $this->errorResponse(
                'Mark cannot exceed total marks (' . $exam->total_marks . ')',
                'VALIDATION_FAILED',
                [],
                422
            );
        }

        try {
            $result = $this->examResultService->saveSingleMark($exam, $validated, auth()->id());

            // Resolve grade for UI feedback
            $gradeInfo = $this->examResultService->resolveGrade($result->percentage);

            return $this->successResponse('Saved', [
                'marks_obtained' => $result->marks_obtained,
                'percentage' => $result->percentage,
                'attendance_status' => $result->attendance_status,
                'letter_grade' => $gradeInfo['letter_grade'] ?? null,
                'is_passing' => $gradeInfo['is_passing'] ?? null,
            ], 'MARK_SAVED');
        } catch (\Exception $e) {
            return $this->errorResponse('Error saving mark: ' . $e->getMessage(), 'ERROR', [], 500);
        }
    }

    /**
     * AJAX: Bulk save all marks (Save All button).
     */
    public function saveAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.marks_obtained' => 'nullable|numeric|min:0',
            'results.*.attendance_status' => 'required|in:present,absent,excused,cheating,incomplete',
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);

        try {
            $results = $this->examResultService->bulkSaveMarks($exam, $validated['results'], auth()->id());
            return $this->successResponse(count($results) . ' results saved successfully.', null, 'MARKS_BULK_SAVED');
        } catch (\Exception $e) {
            return $this->errorResponse('Bulk save failed: ' . $e->getMessage(), 'ERROR', [], 500);
        }
    }

    /**
     * AJAX: Get classes for a grade (cascading filter).
     */
    public function getClasses(Request $request): JsonResponse
    {
        $classes = SchoolClass::where('grade_id', $request->grade_id)->get();

        return $this->successResponse('Classes retrieved', $classes->map(function ($c) {
            return ['id' => $c->id, 'name' => $c->name];
        }), 'CLASSES_LOADED');
    }

    /**
     * AJAX: Get sections for a class (cascading filter).
     */
    public function getSections(Request $request): JsonResponse
    {
        $sections = Section::where('class_id', $request->class_id)->with('schoolClass')->get();

        return $this->successResponse('Sections retrieved', $sections->map(function ($s) {
            return [
                'id'        => $s->id,
                'name'      => $s->name,           // just the section letter e.g. "أ"
                'full_name' => ($s->schoolClass->name ?? '') . ' - ' . $s->name,
            ];
        }), 'SECTIONS_LOADED');
    }

    /**
     * AJAX: Get subjects for a section based on exams (cascading filter).
     */
    public function getSubjects(Request $request): JsonResponse
    {
        $subjects = Subject::whereHas('exams', function ($q) use ($request) {
            $q->where('section_id', $request->section_id)
              ->where('academic_year_id', $request->academic_year_id);
            if ($request->filled('semester_id')) {
                $q->where('semester_id', $request->semester_id);
            }
        })->get();

        return $this->successResponse('Subjects retrieved', $subjects->map(function ($s) {
            return ['id' => $s->id, 'name' => $s->name];
        }), 'SUBJECTS_LOADED');
    }

    /**
     * AJAX: Get exams for filters (cascading filter).
     */
    public function getExams(Request $request): JsonResponse
    {
        $exams = Exam::where('academic_year_id', $request->academic_year_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->when($request->filled('semester_id'), fn($q) => $q->where('semester_id', $request->semester_id))
            ->get();

        return $this->successResponse('Exams retrieved', $exams->map(function ($e) {
            return [
                'id' => $e->id,
                'name' => $e->title . ' (' . ($e->exam_date ? $e->exam_date->format('Y-m-d') : 'No Date') . ') - ' . ucfirst($e->type),
                'total_marks' => $e->total_marks,
            ];
        }), 'EXAMS_LOADED');
    }
}
