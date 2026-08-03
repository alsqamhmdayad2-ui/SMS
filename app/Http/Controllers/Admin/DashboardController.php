<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\Section;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'parents'  => ParentModel::count(),
            'classes'  => SchoolClass::count(),
            'grades'   => Grade::count(),
            'sections' => Section::count(),
        ];

        $recentStudents = Student::latest()->take(5)->get();

        return view('panels.admin.dashboard', compact('stats', 'recentStudents'));
    }
}
