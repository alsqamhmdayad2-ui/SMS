<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\AttendanceRecord;
use App\Services\AttendanceAnalyticsService;
use App\DTOs\AttendanceFilterData;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $attendanceAnalytics
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students'])
            ->first();

        $children = collect();
        $childrenData = [];

        if ($parent) {
            $children = $parent->students;
            $academicYear = \App\Models\AcademicYear::where('status', 1)->first();
            $filter = new AttendanceFilterData(academicYearId: $academicYear?->id);

            $studentId = $request->query('student_id');
            $childrenToProcess = $children;
            
            if ($studentId && $children->contains('id', $studentId)) {
                $childrenToProcess = $children->where('id', $studentId);
            }

            foreach ($childrenToProcess as $child) {
                $stats = $this->attendanceAnalytics->getStudentStats($child->id, $filter);
                
                $records = AttendanceRecord::with(['session'])
                    ->where('student_id', $child->id)
                    ->when($academicYear, function($q) use ($academicYear) {
                        $q->whereHas('session', function($sq) use ($academicYear) {
                            $sq->where('academic_year_id', $academicYear->id);
                        });
                    })
                    ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->orderBy('attendance_sessions.date', 'desc')
                    ->select('attendance_records.*')
                    ->get();
                    
                $childrenData[] = [
                    'child' => $child,
                    'stats' => $stats,
                    'records' => $records
                ];
            }
        }

        return view('panels.parent.attendance', compact('parent', 'children', 'childrenData'));
    }
}
