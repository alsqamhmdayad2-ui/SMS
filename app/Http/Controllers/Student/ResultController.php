<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        protected StudentResultService $studentResultService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->with(['section.schoolClass', 'grade'])->first();

        $academicYear = AcademicYear::where('status', 1)->first();
        $semesters = \App\Models\Semester::all();
        $selectedSemester = $request->get('semester_id');
        $resultData = [];

        if ($student && $academicYear) {
            $resultData = $this->studentResultService->getStudentResult(
                $student, $academicYear->id, $selectedSemester
            );
        }

        return view('panels.student.results', compact('student', 'resultData', 'semesters', 'selectedSemester', 'academicYear'));
    }
}
