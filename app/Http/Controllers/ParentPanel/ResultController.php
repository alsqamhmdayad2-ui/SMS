<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\AcademicYear;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        protected StudentResultService $studentResultService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students'])
            ->first();

        $children = collect();
        $childrenData = [];

        if ($parent) {
            $children = $parent->students;
            $academicYear = AcademicYear::where('status', 1)->first();
            
            $studentId = $request->query('student_id');
            $childrenToProcess = $children;
            
            if ($studentId && $children->contains('id', $studentId)) {
                $childrenToProcess = $children->where('id', $studentId);
            }

            foreach ($childrenToProcess as $child) {
                $resultData = [];
                if ($academicYear) {
                    $resultData = $this->studentResultService->getStudentResult($child, $academicYear->id);
                }
                
                $childrenData[] = [
                    'child' => $child,
                    'resultData' => $resultData
                ];
            }
        }

        return view('panels.parent.results', compact('parent', 'children', 'childrenData'));
    }
}
