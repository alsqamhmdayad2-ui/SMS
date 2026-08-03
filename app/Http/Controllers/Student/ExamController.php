<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ExamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        return view('panels.student.exams', compact('student'));
    }
}
