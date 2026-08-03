<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\ReportCard;
use App\Enums\ReportCardStatus;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        
        $academicYear = AcademicYear::where('status', 1)->first();
        
        $reportCards = collect();
        if ($student && $academicYear) {
            $reportCards = ReportCard::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', ReportCardStatus::Published)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('panels.student.reports', compact('student', 'reportCards'));
    }
}
