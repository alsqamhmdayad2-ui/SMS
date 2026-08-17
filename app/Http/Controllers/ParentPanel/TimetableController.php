<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students'])
            ->firstOrFail();

        $children = $parent->students;
        
        if ($children->isEmpty()) {
            return view('panels.parent.timetable', [
                'parent' => $parent,
                'children' => $children,
                'selectedChild' => null,
                'dailySchedule' => collect(),
                'weeklySchedule' => collect(),
                'daysArabic' => [],
                'currentDay' => now()->englishDayOfWeek,
                'academicYear' => null
            ]);
        }

        $studentId = $request->query('student_id');
        
        if ($studentId) {
            // Authorization check + strict fetch
            $selectedChild = $parent->students()
                ->where('students.id', $studentId)
                ->firstOrFail();
        } else {
            // Default to first child
            $selectedChild = $children->first();
        }

        $academicYear = AcademicYear::where('status', 1)->first();
        
        $currentDay = now()->englishDayOfWeek; // e.g. "Sunday"
        $dailySchedule = collect();
        $weeklySchedule = collect();
        $daysArabic = [
            'Saturday' => 'السبت',
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
        ];

        if ($academicYear && ($selectedChild->class_id || $selectedChild->school_class_id) && $selectedChild->section_id) {
            $scheduleData = \App\Models\Timetable::with(['subject', 'teacher.user'])
                ->where('academic_year_id', $academicYear->id)
                ->where('class_id', $selectedChild->class_id)
                ->where('section_id', $selectedChild->section_id)
                ->orderBy('period_number')
                ->get();
            
            $weeklySchedule = $scheduleData->groupBy('day_of_week');
            $dailySchedule = $weeklySchedule->get($currentDay, $weeklySchedule->get(
                collect([
                    'Sunday' => 'الأحد', 'Saturday' => 'السبت',
                    'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء',
                    'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس',
                ])->get($currentDay, $currentDay),
                collect()
            ));
        }

        return view('panels.parent.timetable', compact(
            'parent', 
            'children', 
            'selectedChild', 
            'dailySchedule', 
            'weeklySchedule', 
            'daysArabic', 
            'currentDay',
            'academicYear'
        ));
    }
}
