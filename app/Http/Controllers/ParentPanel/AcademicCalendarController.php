<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Services\AcademicCalendarService;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    protected AcademicCalendarService $calendarService;

    public function __construct(AcademicCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
        $parent = \App\Models\ParentModel::where('user_id', auth()->id())->first();
        
        if (!$parent) {
            abort(404);
        }

        $children = $parent->students;

        if ($children->isEmpty()) {
            return view('panels.parent.academic-calendar', [
                'children' => $children,
                'selectedStudent' => null,
                'events' => []
            ]);
        }

        $studentId = $request->input('student_id', $children->first()->id);
        
        // Verify that the requested student belongs to this parent
        $selectedStudent = $children->firstWhere('id', $studentId);

        if (!$selectedStudent) {
            abort(404);
        }

        $events = $this->calendarService->getStudentCalendarEvents($selectedStudent);

        return view('panels.parent.academic-calendar', [
            'children' => $children,
            'selectedStudent' => $selectedStudent,
            'events' => $events
        ]);
    }
}
