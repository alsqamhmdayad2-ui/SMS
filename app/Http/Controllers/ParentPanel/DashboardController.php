<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;

class DashboardController extends Controller
{
    public function __construct(
        protected \App\Services\AttendanceAnalyticsService $attendanceService,
        protected \App\Services\StudentResultService $resultService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students.grade', 'students.schoolClass', 'students.section'])
            ->first();

        $children = $parent ? $parent->students : collect();
        $totalChildren = $children->count();

        $academicYear = \App\Models\AcademicYear::where('status', 1)->first();
        $attendanceSummary = 0;
        $totalAttendancePercentage = 0;

        $recentResults = collect();

        if ($academicYear && $totalChildren > 0) {
            $filter = new \App\DTOs\AttendanceFilterData(academicYearId: $academicYear->id);
            foreach ($children as $child) {
                // Calculate average attendance
                $stats = $this->attendanceService->getStudentStats($child->id, $filter);
                $totalAttendancePercentage += $stats['attendance_percentage'] ?? 0;

                // Gather some results (first subject just as a quick recent result)
                $resultData = $this->resultService->getStudentResult($child, $academicYear->id);
                if (!empty($resultData['subjects'])) {
                    $firstResult = collect($resultData['subjects'])->where('is_published', true)->first();
                    if ($firstResult) {
                        $recentResults->push([
                            'child_name' => $child->name,
                            'subject' => $firstResult['subject']->name,
                            'percentage' => $firstResult['total_percentage'],
                            'is_passing' => $firstResult['is_passing'],
                        ]);
                    }
                }
            }
            $attendanceSummary = round($totalAttendancePercentage / $totalChildren, 2);
        }

        return view('panels.parent.dashboard', compact(
            'parent', 
            'children', 
            'totalChildren', 
            'attendanceSummary', 
            'recentResults'
        ));
    }
}
