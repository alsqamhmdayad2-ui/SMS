<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class TeacherDistributionController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        // Default to active year if none selected
        $activeYear = $academicYears->firstWhere('status', true);
        $yearId = $request->query('year_id', $activeYear ? $activeYear->id : null);
        
        $classes = SchoolClass::with('grade')->get();
        $classId = $request->query('class_id');
        
        $sections = collect();
        $subjects = collect();
        $assignments = [];
        $teachersBySubject = [];

        if ($classId && $yearId) {
            $schoolClass = SchoolClass::with('sections')->find($classId);
            if ($schoolClass) {
                $sections = $schoolClass->sections;
                
                // Get all subject_ids linked to these sections for this year
                $sectionIds = $sections->pluck('id')->toArray();
                
                if (!empty($sectionIds)) {
                    $subjectIds = DB::table('subject_section_teacher')
                        ->where('academic_year_id', $yearId)
                        ->whereIn('section_id', $sectionIds)
                        ->pluck('subject_id')
                        ->unique()
                        ->toArray();
                        
                    $subjects = Subject::whereIn('id', $subjectIds)->get();
                    
                    // Fetch existing assignments mapping: [subject_id][section_id] = teacher_id
                    $records = DB::table('subject_section_teacher')
                        ->where('academic_year_id', $yearId)
                        ->whereIn('section_id', $sectionIds)
                        ->get();
                        
                    foreach ($records as $record) {
                        $assignments[$record->subject_id][$record->section_id] = $record->teacher_id;
                    }
                }
            }
            
            // Get teachers by subject qualification
            $teachersBySubject = [];
            foreach ($subjects as $subject) {
                $teachersBySubject[$subject->id] = $subject->qualifiedTeachers()->orderBy('first_name')->get();
            }
        }

        return view('panels.admin.teacher-distributions.index', compact(
            'academicYears', 'yearId', 'classes', 'classId', 'sections', 'subjects', 'assignments', 'teachersBySubject'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'year_id' => 'required|exists:academic_years,id',
            'assignments' => 'nullable|array',
        ]);

        $yearId = $request->input('year_id');
        $assignments = $request->input('assignments', []);
        
        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($assignments as $subjectId => $sectionsData) {
                foreach ($sectionsData as $sectionId => $teacherId) {
                    $tId = !empty($teacherId) ? $teacherId : null;
                    
                    DB::table('subject_section_teacher')->updateOrInsert(
                        [
                            'academic_year_id' => $yearId,
                            'subject_id' => $subjectId, 
                            'section_id' => $sectionId
                        ],
                        [
                            'teacher_id' => $tId, 
                            'updated_at' => now()
                        ]
                    );
                    $count++;
                }
            }
            
            DB::commit();
            return back()->with('success', "تم تحديث تعيينات المعلمين بنجاح (تم حفظ {$count} توزيع).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء حفظ التوزيع: ' . $e->getMessage());
        }
    }
}
