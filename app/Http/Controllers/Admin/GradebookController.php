<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AssessmentComponent;
use App\Services\GradebookService;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function __construct(protected GradebookService $gradebookService) {}

    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $grades = Grade::all();
        $subjects = Subject::all();

        $sections = collect();
        if ($request->filled('grade_id')) {
            $sections = Section::whereHas('schoolClass', fn($q) => $q->where('grade_id', $request->grade_id))
                ->with('schoolClass')->get();
        }

        $gradebook = collect();
        $components = collect();
        $stats = null;

        if ($request->filled(['academic_year_id', 'subject_id', 'section_id'])) {
            $gradebook = $this->gradebookService->getGradebook(
                $request->academic_year_id,
                $request->semester_id,
                $request->subject_id,
                $request->section_id
            );
            $stats = $this->gradebookService->getClassStats($gradebook);

            $components = AssessmentComponent::where('academic_year_id', $request->academic_year_id)
                ->where('subject_id', $request->subject_id)
                ->where('status', true)
                ->orderBy('order')
                ->get();
        }

        return view('panels.admin.exams.gradebook.index', compact(
            'academicYears', 'semesters', 'grades', 'subjects', 'sections',
            'gradebook', 'components', 'stats'
        ));
    }

    public function studentBreakdown(Request $request)
    {
        $breakdown = $this->gradebookService->getStudentBreakdown(
            $request->student_id,
            $request->academic_year_id,
            $request->semester_id,
            $request->subject_id,
            $request->section_id
        );

        if (!$breakdown) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($breakdown);
    }
}
