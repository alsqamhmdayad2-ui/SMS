<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)
            ->with(['grade', 'schoolClass', 'section', 'parent'])
            ->first();

        return view('panels.student.profile', compact('student'));
    }
}
