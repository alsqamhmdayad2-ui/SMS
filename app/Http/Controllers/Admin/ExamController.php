<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ExamService;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Enums\ExamStatus;

class ExamController extends Controller
{
    public function __construct(
        protected ExamService $examService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['academic_year_id', 'semester_id', 'subject_id', 'section_id', 'status']);
        $exams = $this->examService->getAll($filters);
        
        $data = $this->getFormData();
        $data['exams'] = $exams;
        
        return view('panels.admin.exams.index', $data);
    }

    public function create()
    {
        $data = $this->getFormData();
        return view('panels.admin.exams.create', $data);
    }

    public function store(StoreExamRequest $request)
    {
        try {
            $data = $request->validated();
            $exam = $this->examService->create($data);

            return redirect()->route('admin.exams.show', $exam->id)
                ->with('success', 'تم إنشاء الاختبار بنجاح.. You can now build questions.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Exam $exam)
    {
        $exam->load(['academicYear', 'semester', 'grade', 'schoolClass', 'section', 'subject', 'teacher', 'questions.options']);
        return view('panels.admin.exams.builder', compact('exam'));
    }

    public function print(Exam $exam)
    {
        $exam->load(['academicYear', 'semester', 'grade', 'schoolClass', 'section', 'subject', 'teacher', 'questions.options']);
        return view('panels.admin.exams.print', compact('exam'));
    }

    public function publish(Exam $exam)
    {
        try {
            $this->examService->publish($exam);
            return back()->with('success', 'Exam published and locked successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function unlock(Exam $exam)
    {
        $exam->update(['status' => ExamStatus::DRAFT->value]);
        return back()->with('success', 'Exam unlocked. You can now modify questions.');
    }

    public function edit(Exam $exam)
    {
        $data = $this->getFormData();
        $data['exam'] = $exam;
        return view('panels.admin.exams.edit', $data);
    }

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        try {
            $this->examService->update($exam, $request->validated());

            return redirect()->route('admin.exams.index')
                ->with('success', 'تم تحديث الاختبار بنجاح..');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Exam $exam)
    {
        try {
            $this->examService->delete($exam);
            return back()->with('success', 'تم حذف الاختبار بنجاح..');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    protected function getFormData()
    {
        return [
            'academicYears' => AcademicYear::where('status', true)->get(),
            'semesters' => Semester::where('status', true)->get(),
            'grades' => Grade::all(),
            'classes' => SchoolClass::all(),
            'sections' => Section::all(),
            'subjects' => Subject::where('status', true)->get(),
            'teachers' => Teacher::all(),
            'statuses' => ExamStatus::cases(),
        ];
    }
}
