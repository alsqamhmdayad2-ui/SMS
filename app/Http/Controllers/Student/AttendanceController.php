<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\AttendanceService;
use App\Services\AttendanceAnalyticsService;
use App\DTOs\AttendanceFilterData;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceAnalyticsService $analyticsService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        
        // Fetch student record since user->student relation might not be direct in User model
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $activeYear = AcademicYear::where('status', true)->first();
        
        $attendanceRecords = collect();
        $stats = [
            'total_sessions'        => 0,
            'present_count'         => 0,
            'absent_count'          => 0,
            'late_count'            => 0,
            'excused_count'         => 0,
            'attendance_percentage' => 0,
            'present_percentage'    => 0,
            'absent_percentage'     => 0,
        ];
        
        if ($activeYear) {
            $attendanceRecords = $this->attendanceService->getStudentAttendance(
                $student->id,
                $activeYear->id
            );
            
            $filters = new AttendanceFilterData(
                academicYearId: $activeYear->id
            );
            
            $stats = $this->analyticsService->getStudentStats($student->id, $filters);
        }

        return view('panels.student.attendance', compact('attendanceRecords', 'stats', 'activeYear'));
    }
}
