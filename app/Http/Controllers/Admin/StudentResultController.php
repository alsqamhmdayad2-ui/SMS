<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function __construct(protected StudentResultService $resultService) {}

    public function index(Request $request)
    {
        $students = Student::with(['grade', 'schoolClass', 'section'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('family_name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('section', fn($s) => $s->where('class_id', $request->class_id)))
            ->orderBy('first_name')
            ->paginate(20);

        $sections = Section::with('schoolClass')->orderBy('name')->get();
        $schoolClasses = SchoolClass::with('grade')->orderBy('name')->get();

        return view('panels.admin.exams.student-results.index', compact('students', 'sections', 'schoolClasses'));
    }

    public function show(Request $request, Student $student)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $selectedYear = $request->get('academic_year_id', AcademicYear::where('status', true)->first()->id ?? null);
        $selectedSemester = $request->get('semester_id');

        $result = null;
        if ($selectedYear) {
            $result = $this->resultService->getStudentResult($student, $selectedYear, $selectedSemester, true);
        }

        return view('panels.admin.exams.student-results.show', compact(
            'student', 'academicYears', 'semesters', 'selectedYear', 'selectedSemester', 'result'
        ));
    }

    public function printResult(Request $request, Student $student)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $allMarks = \App\Models\StudentSemesterMark::with('subject')
            ->where('academic_year_id', $academicYear->id)
            ->where('student_id', $student->id)
            ->get();

        $subjectsData = [];
        $totalObtained = 0;
        $totalMax = 0;
        
        $certificateType = $request->semester_id ? 'semester' : 'annual';

        // Group marks by subject
        $groupedBySubject = $allMarks->groupBy('subject_id');

        foreach ($groupedBySubject as $subjectId => $marksForSubject) {
            $subjectName = $marksForSubject->first()->subject->name ?? 'غير معروف';
            
            $sem1Mark = $marksForSubject->where('semester_id', 1)->first(); // Assuming ID 1 is sem 1
            $sem2Mark = $marksForSubject->where('semester_id', 2)->first(); // Assuming ID 2 is sem 2
            
            $sem1Total = $sem1Mark ? $sem1Mark->total : null;
            $sem2Total = $sem2Mark ? $sem2Mark->total : null;
            
            // For Annual, sem1 is /2 (out of 50), sem2 is /2 (out of 50)
            $sem1Out50 = $sem1Total !== null ? round($sem1Total / 2, 2) : null;
            $sem2Out50 = $sem2Total !== null ? round($sem2Total / 2, 2) : null;
            
            $annualTotal = 0;
            if ($sem1Out50 !== null) $annualTotal += $sem1Out50;
            if ($sem2Out50 !== null) $annualTotal += $sem2Out50;

            $subjectsData[] = [
                'name' => $subjectName,
                'sem1_100' => $sem1Total,
                'sem1_50' => $sem1Out50,
                'sem1_grade' => $this->getLetterGrade($sem1Total),
                
                'sem2_100' => $sem2Total,
                'sem2_50' => $sem2Out50,
                'sem2_grade' => $this->getLetterGrade($sem2Total),
                
                'annual_total' => $annualTotal,
                'annual_grade' => $this->getLetterGrade($annualTotal), 
            ];

            if ($certificateType === 'annual') {
                $totalObtained += $annualTotal;
                $totalMax += 100;
            } else {
                $currentSemMark = $request->semester_id == 1 ? $sem1Total : $sem2Total;
                if ($currentSemMark !== null) {
                    $totalObtained += $currentSemMark;
                    $totalMax += 100;
                }
            }
        }

        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

        $studentData = [
            [
                'student' => $student,
                'subjects' => $subjectsData,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'final_grade' => $this->getLetterGrade($percentage),
                'is_passing' => $percentage >= 50
            ]
        ];

        $pdf = \Mccarlosen\LaravelMpdf\Facades\LaravelMpdf::loadView('panels.admin.reports.report-cards.pdf', [
            'studentsData' => $studentData,
            'section' => $student->section,
            'academicYear' => $academicYear,
            'certificateType' => $certificateType,
            'semester' => $request->semester_id ? Semester::find($request->semester_id) : null
        ], [], [
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'format' => 'A4',
            'orientation' => 'P'
        ]);
        
        $filename = 'شهادة_' . str_replace(' ', '_', $student->full_name) . '.pdf';
        
        return $pdf->stream($filename);
    }

    private function getLetterGrade($mark)
    {
        if ($mark === null) return '-';
        if ($mark >= 90) return 'ممتاز';
        if ($mark >= 80) return 'جيد جداً';
        if ($mark >= 70) return 'جيد';
        if ($mark >= 60) return 'مقبول';
        if ($mark >= 50) return 'ضعيف';
        return 'راسب';
    }
}
