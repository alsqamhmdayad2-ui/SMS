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

    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        
        $academicYear = AcademicYear::where('status', 1)->first();
        $resultData = [];
        
        if ($student && $academicYear) {
            $resultData = $this->studentResultService->getStudentResult($student, $academicYear->id);
        }

        return view('panels.student.results', compact('student', 'resultData'));
    }
}
