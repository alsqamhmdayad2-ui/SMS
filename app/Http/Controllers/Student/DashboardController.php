<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Services\AttendanceAnalyticsService;
use App\Services\StudentResultService;
use App\DTOs\AttendanceFilterData;

class DashboardController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $attendanceService,
        protected StudentResultService $resultService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)
            ->with(['grade', 'schoolClass', 'section', 'parent'])
            ->first();

        if (!$student) {
            // If the user has a student role but no student record, log them out to prevent them getting stuck
            auth()->logout();
            return redirect()->route('login')->with('error', 'حساب الطالب غير مكتمل البيانات. يرجى مراجعة الإدارة.');
        }

        $academicYear = AcademicYear::where('status', 1)->first();
        
        // Attendance Summary
        $attendanceStats = ['attendance_percentage' => 0];
        if ($academicYear) {
            $filter = new AttendanceFilterData(academicYearId: $academicYear->id);
            $attendanceStats = $this->attendanceService->getStudentStats($student->id, $filter);
        }

        // Academic Results Summary
        $resultsSummary = [
            'overall_gpa' => null,
            'average_percentage' => 0,
            'total_subjects' => 0,
        ];
        $subjectResults = [];
        if ($academicYear) {
            $resultData = $this->resultService->getStudentResult($student, $academicYear->id);
            $resultsSummary = $resultData['summary'] ?? $resultsSummary;
            $subjectResults = $resultData['subjects'] ?? [];
        }

        // Upcoming Exams (Next 5 exams)
        $upcomingExams = collect();
        if ($academicYear && $student->section_id) {
            $upcomingExams = Exam::with(['subject', 'teacher.user'])
                ->where('academic_year_id', $academicYear->id)
                ->where('section_id', $student->section_id)
                ->where('status', 'published')
                ->where('exam_date', '>=', now()->toDateString())
                ->orderBy('exam_date', 'asc')
                ->take(5)
                ->get();
        }

        // Timetable (Schedule)
        $currentDay = now()->englishDayOfWeek; // e.g. "Sunday"
        $dailySchedule = collect();
        $weeklySchedule = collect();
        $daysArabic = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
        ];

        if ($academicYear && $student->school_class_id && $student->section_id) {
            $scheduleData = \App\Models\Timetable::with(['subject', 'teacher.user'])
                ->where('academic_year_id', $academicYear->id)
                ->where('class_id', $student->school_class_id)
                ->where('section_id', $student->section_id)
                ->orderBy('period_number')
                ->get();
            
            $weeklySchedule = $scheduleData->groupBy('day_of_week');
            $dailySchedule = $weeklySchedule->get($currentDay, collect());
        }

        return view('panels.student.dashboard', compact('student', 'academicYear', 'attendanceStats', 'resultsSummary', 'subjectResults', 'upcomingExams', 'dailySchedule', 'weeklySchedule', 'daysArabic', 'currentDay'));
    }
}
