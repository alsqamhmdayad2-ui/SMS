<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function __construct(protected StudentResultService $resultService) {}

    public function index(Request $request)
    {
        $students = Student::with(['grade', 'schoolClass', 'section'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->filled('grade_id'), fn($q) => $q->where('grade_id', $request->grade_id))
            ->orderBy('name')
            ->paginate(20);

        return view('panels.admin.exams.student-results.index', compact('students'));
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
