<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        return view('panels.student.settings', compact('student'));
    }
}
