<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudyPlanController extends Controller
{
    /**
     * Display a listing of classes to select for study plan.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::with('grade')->where('status', 1)->get();
        $selectedClass = $request->class_id ? SchoolClass::with('grade', 'subjects')->find($request->class_id) : null;
        
        $subjects = [];
        if ($selectedClass) {
            $subjects = $selectedClass->subjects; // gets pivot data including weekly_periods
        }

        return view('panels.admin.study-plans.index', compact('classes', 'selectedClass', 'subjects'));
    }

    /**
     * Save the updated weekly periods for a class.
     */
    public function save(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'weekly_periods' => 'required|array',
            'weekly_periods.*' => 'integer|min:0|max:40'
        ]);

        $classId = $request->class_id;

        DB::beginTransaction();
        try {
            foreach ($request->weekly_periods as $subjectId => $periods) {
                // Update the pivot table for this class and subject
                DB::table('class_subject_teacher')
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->update(['weekly_periods' => $periods, 'updated_at' => now()]);
            }
            DB::commit();

            return redirect()->route('admin.study-plans.index', ['class_id' => $classId])
                             ->with('success', 'تم تحديث الخطة الدراسية بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حفظ الخطة: ' . $e->getMessage());
        }
    }

    /**
     * Display all classes for a specific subject (bulk edit by subject).
     */
    public function bySubject(Request $request)
    {
        $subjects = Subject::orderBy('name')->get();
        $selectedSubject = $request->subject_id ? Subject::find($request->subject_id) : null;

        $classPlans = collect();
        if ($selectedSubject) {
            $classPlans = DB::table('class_subject_teacher as cst')
                ->join('classes', 'classes.id', '=', 'cst.class_id')
                ->join('grades', 'grades.id', '=', 'classes.grade_id')
                ->where('cst.subject_id', $selectedSubject->id)
                ->select(
                    'cst.class_id',
                    'cst.weekly_periods',
                    'classes.name as class_name',
                    'grades.name as grade_name'
                )
                ->orderBy('grades.id')
                ->orderBy('classes.name')
                ->get()
                ->unique('class_id');
        }

        return view('panels.admin.study-plans.by-subject', compact('subjects', 'selectedSubject', 'classPlans'));
    }

    /**
     * Save weekly periods for all classes of a specific subject at once.
     */
    public function saveBySubject(Request $request)
    {
        $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'weekly_periods'   => 'required|array',
            'weekly_periods.*' => 'integer|min:0|max:40',
        ]);

        $subjectId = $request->subject_id;

        DB::beginTransaction();
        try {
            foreach ($request->weekly_periods as $classId => $periods) {
                DB::table('class_subject_teacher')
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->update(['weekly_periods' => $periods, 'updated_at' => now()]);
            }
            DB::commit();

            return redirect()->route('admin.study-plans.by-subject', ['subject_id' => $subjectId])
                             ->with('success', 'تم تحديث الخطة الدراسية لجميع الصفوف بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
