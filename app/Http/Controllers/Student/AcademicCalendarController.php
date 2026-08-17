<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AcademicCalendarService;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(AcademicCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display the academic calendar for the student.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();

        $events = [];
        if ($student) {
            $events = $this->calendarService->getStudentCalendarEvents($student);
        }

        return view('panels.student.academic-calendar', compact('events'));
    }
}
