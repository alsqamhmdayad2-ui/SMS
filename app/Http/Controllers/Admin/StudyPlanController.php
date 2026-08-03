<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
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
}
