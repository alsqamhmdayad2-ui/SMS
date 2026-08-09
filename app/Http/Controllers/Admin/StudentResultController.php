<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function __construct(protected StudentResultService $resultService) {}

    public function index(Request $request)
    {
        $students = Student::with(['grade', 'schoolClass', 'section'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('family_name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('section', fn($s) => $s->where('class_id', $request->class_id)))
            ->orderBy('first_name')
            ->paginate(20);

        $sections = Section::with('schoolClass')->orderBy('name')->get();
        $schoolClasses = SchoolClass::with('grade')->orderBy('name')->get();

        return view('panels.admin.exams.student-results.index', compact('students', 'sections', 'schoolClasses'));
    }

    public function show(Request $request, Student $student)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $selectedYear = $request->get('academic_year_id', AcademicYear::where('status', true)->first()->id ?? null);
        $selectedSemester = $request->get('semester_id');

        $result = null;
        if ($selectedYear) {
            $result = $this->resultService->getStudentResult($student, $selectedYear, $selectedSemester);
        }

        return view('panels.admin.exams.student-results.show', compact(
            'student', 'academicYears', 'semesters', 'selectedYear', 'selectedSemester', 'result'
        ));
    }

    public function printResult(Request $request, Student $student)
    {
        $selectedYear = $request->get('academic_year_id', AcademicYear::where('status', true)->first()->id ?? null);
        $selectedSemester = $request->get('semester_id');

        $result = null;
        $academicYear = null;
        $semester = null;

        if ($selectedYear) {
            $result = $this->resultService->getStudentResult($student, $selectedYear, $selectedSemester);
            $academicYear = AcademicYear::find($selectedYear);
            $semester = $selectedSemester ? Semester::find($selectedSemester) : null;
        }

        return view('panels.admin.exams.student-results.print', compact(
            'student', 'result', 'academicYear', 'semester'
        ));
    }
}
