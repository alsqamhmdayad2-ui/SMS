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
    public function index(\App\Services\AttendanceAnalyticsService $attendanceAnalytics)
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

        // Active Academic Year & Semester
        $activeYear = \App\Models\AcademicYear::where('status', 1)->first();
        $activeSemester = \App\Models\Semester::where('status', 1)->first();

        // Today's Attendance Stats
        $todayStats = null;
        if ($activeYear) {
            $todayStats = $attendanceAnalytics->getDailySummary(date('Y-m-d'), $activeYear->id);
        }

        return view('panels.admin.dashboard', compact('stats', 'recentStudents', 'activeYear', 'activeSemester', 'todayStats'));

    }
}
