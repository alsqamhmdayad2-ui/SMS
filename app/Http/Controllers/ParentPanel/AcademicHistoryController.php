<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class AcademicHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students'])
            ->firstOrFail();

        $children = $parent->students;
        
        if ($children->isEmpty()) {
            return view('panels.parent.academic-history', [
                'parent' => $parent,
                'children' => $children,
                'selectedChild' => null,
                'enrollments' => collect()
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

        $enrollments = collect();
        if ($selectedChild) {
            $enrollments = StudentEnrollment::with(['academicYear', 'grade', 'schoolClass', 'section'])
                ->where('student_id', $selectedChild->id)
                ->get()
                ->sortBy(function ($enrollment) {
                    return optional($enrollment->academicYear)->start_date;
                })
                ->values();
        }

        return view('panels.parent.academic-history', compact('parent', 'children', 'selectedChild', 'enrollments'));
    }
}
