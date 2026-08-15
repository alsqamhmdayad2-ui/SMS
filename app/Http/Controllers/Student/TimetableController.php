<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\TimetableService;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function __construct(
        private TimetableService $timetableService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        
        // Fetch student record since user->student relation is not defined in User model
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $timetable = $this->timetableService->getBySection(
            $student->section_id
        );

        return view('panels.student.timetable', compact('timetable'));
    }
}
