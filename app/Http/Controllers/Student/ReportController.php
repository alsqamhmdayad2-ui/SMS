<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        $academicYear = AcademicYear::where('status', 1)->first();

        $records = collect();
        $stats = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'percentage' => 0];

        if ($student && $academicYear) {
            $records = AttendanceRecord::with(['session.subject'])
                ->where('student_id', $student->id)
                ->whereHas('session', fn($q) => $q->where('academic_year_id', $academicYear->id))
                ->when($request->filled('semester_id'), fn($q) =>
                    $q->whereHas('session', fn($s) => $s->where('semester_id', $request->semester_id))
                )
                ->orderByDesc('created_at')
                ->get();

            $stats['total']   = $records->count();
            $stats['present'] = $records->where('status', 'present')->count();
            $stats['absent']  = $records->where('status', 'absent')->count();
            $stats['late']    = $records->where('status', 'late')->count();
            $stats['percentage'] = $stats['total'] > 0
                ? round(($stats['present'] / $stats['total']) * 100, 1)
                : 0;
        }

        $semesters = \App\Models\Semester::all();

        return view('panels.student.reports', compact('student', 'records', 'stats', 'semesters', 'academicYear'));
    }
}
