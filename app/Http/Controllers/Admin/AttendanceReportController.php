<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DTOs\AttendanceFilterData;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceReportService;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $analytics,
        protected AttendanceReportService    $reportService
    ) {}

    /**
     * Dashboard — today's summary + top absentees + lowest sections.
     */
    public function dashboard(Request $request)
    {
        $today   = Carbon::today()->toDateString();
        $filters = AttendanceFilterData::fromRequest(array_merge(['date' => $today], $request->all()));

        $todayStats    = $this->analytics->getDailySummary($today, $filters->academicYearId);
        $topAbsentees  = $this->analytics->getTopAbsentees($filters, 5);
        $sectionRanks  = $this->analytics->getSectionRankings($filters);
        $lowestSections = array_slice($sectionRanks, 0, 5); // Lowest attendance first

        $academicYears = AcademicYear::all();

        return view('panels.admin.attendance.reports.dashboard', compact(
            'todayStats', 'topAbsentees', 'lowestSections', 'academicYears', 'today', 'filters'
        ));
    }

    /**
     * Student Report.
     */
    public function studentReport(Request $request)
    {
        $filters = AttendanceFilterData::fromRequest($request->all());
        $data    = null;

        if ($filters->studentId) {
            $data = $this->reportService->getStudentReport($filters->studentId, $filters);
        }

        $students      = Student::all();
        $academicYears = AcademicYear::all();
        $semesters     = Semester::all();

        return view('panels.admin.attendance.reports.student', compact('data', 'filters', 'students', 'academicYears', 'semesters'));
    }

    /**
     * Section Report.
     */
    public function sectionReport(Request $request)
    {
        $filters = AttendanceFilterData::fromRequest($request->all());
        $data    = null;

        if ($filters->sectionId) {
            $data = $this->reportService->getSectionReport($filters->sectionId, $filters);
        }

        $sections      = Section::with('grade')->get();
        $academicYears = AcademicYear::all();
        $semesters     = Semester::all();

        return view('panels.admin.attendance.reports.section', compact('data', 'filters', 'sections', 'academicYears', 'semesters'));
    }

    /**
     * Teacher Report.
     */
    public function teacherReport(Request $request)
    {
        $filters = AttendanceFilterData::fromRequest($request->all());
        $data    = null;

        if ($filters->teacherId) {
            $data = $this->reportService->getTeacherReport($filters->teacherId, $filters);
        }

        $teachers      = Teacher::all();
        $academicYears = AcademicYear::all();
        $semesters     = Semester::all();

        return view('panels.admin.attendance.reports.teacher', compact('data', 'filters', 'teachers', 'academicYears', 'semesters'));
    }

    /**
     * Daily Report.
     */
    public function dailyReport(Request $request)
    {
        $filters = AttendanceFilterData::fromRequest($request->all());
        $date    = $filters->date ?? Carbon::today()->toDateString();
        $data    = $this->reportService->getDailySummary($date, $filters);

        $academicYears = AcademicYear::all();

        return view('panels.admin.attendance.reports.daily', compact('data', 'filters', 'academicYears', 'date'));
    }

    /**
     * Monthly Report.
     */
    public function monthlyReport(Request $request)
    {
        $filters = AttendanceFilterData::fromRequest($request->all());
        $month   = $filters->month ?? Carbon::today()->format('Y-m');
        $data    = $this->reportService->getMonthlySummary($month, $filters);

        $sections      = Section::with('grade')->get();
        $academicYears = AcademicYear::all();

        return view('panels.admin.attendance.reports.monthly', compact('data', 'filters', 'sections', 'academicYears', 'month'));
    }
}
