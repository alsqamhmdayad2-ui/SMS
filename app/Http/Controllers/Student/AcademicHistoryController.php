<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class AcademicHistoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Fetch enrollments and eager load verified relationships
        $enrollments = StudentEnrollment::with(['academicYear', 'grade', 'schoolClass', 'section'])
            ->where('student_id', $student->id)
            ->get()
            // Sort by academic year start date to ensure chronological order
            ->sortBy(function ($enrollment) {
                return optional($enrollment->academicYear)->start_date;
            })
            ->values();

        return view('panels.student.academic-history', compact('enrollments'));
    }
}
