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
use App\Exporters\PdfExporter;
use App\Exporters\ExcelExporter;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $analytics,
        protected AttendanceReportService    $reportService,
        protected PdfExporter                $pdfExporter,
        protected ExcelExporter              $excelExporter
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
        $view = 'panels.admin.attendance.reports.student';

        $action = $request->input('action', 'view');
        if ($action === 'pdf' && $data) {
            return $this->pdfExporter->export(['data' => $data, 'filters' => $filters, 'title' => 'تقرير حضور طالب'], $view, null, $request->all());
        } elseif ($action === 'excel' && $data) {
            $export = new \App\Exports\AttendanceStudentExport($data, $filters);
            return $this->excelExporter->exportXlsx($export, 'student_attendance_' . now()->format('Y-m-d_His'));
        }

        return view($view, compact('data', 'filters', 'students', 'academicYears', 'semesters'));
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
        $view = 'panels.admin.attendance.reports.section';

        $action = $request->input('action', 'view');
        if ($action === 'pdf' && $data) {
            return $this->pdfExporter->export(['data' => $data, 'filters' => $filters, 'title' => 'تقرير حضور شعبة'], $view, null, $request->all());
        } elseif ($action === 'excel' && $data) {
            $export = new \App\Exports\AttendanceSectionExport($data, $filters);
            return $this->excelExporter->exportXlsx($export, 'section_attendance_' . now()->format('Y-m-d_His'));
        }

        return view($view, compact('data', 'filters', 'sections', 'academicYears', 'semesters'));
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
        $view = 'panels.admin.attendance.reports.daily';

        $action = $request->input('action', 'view');
        if ($action === 'pdf' && $data) {
            return $this->pdfExporter->export(['data' => $data, 'filters' => $filters, 'date' => $date, 'title' => 'التقرير اليومي للحضور'], $view, null, $request->all());
        } elseif ($action === 'excel' && $data) {
            $export = new \App\Exports\AttendanceDailyExport($data, $filters, $date);
            return $this->excelExporter->exportXlsx($export, 'daily_attendance_' . now()->format('Y-m-d_His'));
        }

        return view($view, compact('data', 'filters', 'academicYears', 'date'));
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
        $view = 'panels.admin.attendance.reports.monthly';

        $action = $request->input('action', 'view');
        if ($action === 'pdf' && $data) {
            return $this->pdfExporter->export(['data' => $data, 'filters' => $filters, 'month' => $month, 'title' => 'التقرير الشهري للحضور'], $view, null, $request->all());
        } elseif ($action === 'excel' && $data) {
            $export = new \App\Exports\AttendanceMonthlyExport($data, $filters, $month);
            return $this->excelExporter->exportXlsx($export, 'monthly_attendance_' . now()->format('Y-m-d_His'));
        }

        return view($view, compact('data', 'filters', 'sections', 'academicYears', 'month'));
    }
}
