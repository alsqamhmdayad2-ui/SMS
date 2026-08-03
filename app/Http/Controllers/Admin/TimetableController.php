<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::with('sections.schoolClass.grade')->get();
        $selectedSection = $request->section_id ? Section::with('schoolClass.grade')->find($request->section_id) : null;

        $timetables = collect();
        $weeklySchedule = [];
        $periods = [];
        
        $days = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('status', true)->first();

        if ($selectedSection && $academicYear && $semester) {
            $timetables = Timetable::with(['subject', 'teacher'])
                ->where('section_id', $selectedSection->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->get();
            
            // Determine max periods based on grade
            $gradeName = $selectedSection->schoolClass->grade->name ?? '';
            // If primary (1-4) -> 5 periods. Otherwise 6.
            // A simple heuristic: check if grade name contains 1,2,3,4 or 'أول', 'ثاني', 'ثالث', 'رابع'.
            // For now, let's look at the class name or default to 6 if we can't tell, or we can just show max 6 always and grey out the 6th if they only need 5.
            // Actually, we'll determine the max period from the DB, or default to 6.
            $maxPeriod = 6;
            if (preg_match('/(الأول|الثاني|الثالث|الرابع|1|2|3|4)/u', $selectedSection->schoolClass->name)) {
                $maxPeriod = 5;
            }

            $periods = range(1, $maxPeriod);

            foreach ($days as $day) {
                foreach ($periods as $period) {
                    $item = $timetables->firstWhere(function ($t) use ($day, $period) {
                        return $t->day_of_week === $day && $t->period_number === $period;
                    });
                    $weeklySchedule[$day][$period] = $item;
                }
            }
        }

        return view('panels.admin.timetables.index', compact('classes', 'selectedSection', 'weeklySchedule', 'periods', 'days'));
    }

    public function build(Request $request)
    {
        $classes = SchoolClass::with('sections.schoolClass.grade')->get();
        $selectedSection = $request->section_id ? Section::with('schoolClass.grade')->find($request->section_id) : null;

        $weeklySchedule = [];
        $periods = [];
        $days = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $sectionSubjects = [];

        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('status', true)->first();

        if ($selectedSection && $academicYear && $semester) {
            // Load existing timetable to populate the form
            $timetables = Timetable::where('section_id', $selectedSection->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->get();
            
            // Get subjects assigned to this section (using subject_section_teacher pivot)
            $sectionSubjects = DB::table('subject_section_teacher')
                ->join('subjects', 'subjects.id', '=', 'subject_section_teacher.subject_id')
                ->leftJoin('teachers', 'teachers.id', '=', 'subject_section_teacher.teacher_id')
                ->where('subject_section_teacher.section_id', $selectedSection->id)
                ->select('subjects.id as subject_id', 'subjects.name as subject_name', 'teachers.id as teacher_id', 'teachers.first_name', 'teachers.family_name')
                ->get();

            // Determine periods (5 or 6)
            $maxPeriod = 6;
            if (preg_match('/(الأول|الثاني|الثالث|الرابع|1|2|3|4)/u', $selectedSection->schoolClass->name)) {
                $maxPeriod = 5;
            }
            $periods = range(1, $maxPeriod);

            foreach ($days as $day) {
                foreach ($periods as $period) {
                    $item = $timetables->firstWhere(function ($t) use ($day, $period) {
                        return $t->day_of_week === $day && $t->period_number === $period;
                    });
                    $weeklySchedule[$day][$period] = $item ? $item->subject_id : null;
                }
            }
        }

        return view('panels.admin.timetables.build', compact('classes', 'selectedSection', 'weeklySchedule', 'periods', 'days', 'sectionSubjects'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'schedule'   => 'required|array',
        ]);

        $section = Section::with('schoolClass')->findOrFail($request->section_id);
        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('status', true)->first();

        if (!$academicYear || !$semester) {
            return back()->with('error', 'الرجاء تفعيل سنة دراسية وفصل دراسي أولاً.');
        }

        // Get teacher mappings for this section
        $teacherMappings = DB::table('subject_section_teacher')
            ->where('section_id', $section->id)
            ->pluck('teacher_id', 'subject_id')
            ->toArray();

        DB::beginTransaction();
        try {
            // Clear existing schedule for this section in the current active semester
            Timetable::where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->delete();

            // Insert new schedule
            $inserts = [];
            $now = now();
            foreach ($request->schedule as $day => $periods) {
                foreach ($periods as $periodNumber => $subjectId) {
                    if ($subjectId) {
                        $inserts[] = [
                            'academic_year_id' => $academicYear->id,
                            'semester_id'      => $semester->id,
                            'grade_id'         => $section->schoolClass->grade_id,
                            'class_id'         => $section->class_id,
                            'section_id'       => $section->id,
                            'subject_id'       => $subjectId,
                            'teacher_id'       => $teacherMappings[$subjectId] ?? null, // Auto-assign teacher
                            'day_of_week'      => $day,
                            'period_number'    => $periodNumber,
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ];
                    }
                }
            }

            if (!empty($inserts)) {
                Timetable::insert($inserts);
            }

            DB::commit();
            return redirect()->route('admin.timetables.index', ['section_id' => $section->id])
                ->with('success', 'تم حفظ الجدول الدراسي بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حفظ الجدول: ' . $e->getMessage());
        }
    }

    public function checkConflict(Request $request)
    {
        $teacherId = $request->teacher_id;
        $dayOfWeek = $request->day_of_week;
        $periodNumber = $request->period_number;
        $sectionId = $request->section_id;

        if (!$teacherId || !$dayOfWeek || !$periodNumber) {
            return response()->json(['hasConflict' => false]);
        }

        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('status', true)->first();

        if (!$academicYear || !$semester) {
            return response()->json(['hasConflict' => false]);
        }

        $conflict = Timetable::with('section.schoolClass')
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('period_number', $periodNumber)
            ->where('section_id', '!=', $sectionId)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->first();

        if ($conflict) {
            $className = $conflict->section->schoolClass->name ?? '';
            $sectionName = $conflict->section->name ?? '';
            return response()->json([
                'hasConflict' => true,
                'message' => "المعلم مشغول في هذا الوقت مع {$className} - {$sectionName}"
            ]);
        }

        return response()->json(['hasConflict' => false]);
    }
}
