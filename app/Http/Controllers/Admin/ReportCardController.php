<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentSemesterMark;
use Illuminate\Http\Request;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;

class ReportCardController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $grades = Grade::all();
        
        return view('panels.admin.reports.report-cards.index', compact('academicYears', 'semesters', 'grades'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'certificate_type' => 'required|in:semester,annual',
        ]);

        $section = Section::with(['schoolClass.grade'])->findOrFail($request->section_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        // Find students in this section
        $students = Student::where('section_id', $section->id)->orderBy('first_name')->get();
        if ($students->isEmpty()) {
            return back()->with('error', 'لا يوجد طلاب في هذه الشعبة.');
        }

        $allMarks = StudentSemesterMark::with('subject')
            ->where('academic_year_id', $academicYear->id)
            ->where('section_id', $section->id)
            ->get();

        $studentData = [];

        foreach ($students as $student) {
            $studentMarks = $allMarks->where('student_id', $student->id);
            
            $subjectsData = [];
            $totalObtained = 0;
            $totalMax = 0;

            // Group marks by subject
            $groupedBySubject = $studentMarks->groupBy('subject_id');

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
                    'annual_grade' => $this->getLetterGrade($annualTotal), // Since annual is out of 100
                ];

                if ($request->certificate_type === 'annual') {
                    $totalObtained += $annualTotal;
                    $totalMax += 100;
                } else {
                    // Semester specific
                    $currentSemMark = $request->semester_id == 1 ? $sem1Total : $sem2Total;
                    if ($currentSemMark !== null) {
                        $totalObtained += $currentSemMark;
                        $totalMax += 100;
                    }
                }
            }

            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

            $studentData[] = [
                'student' => $student,
                'subjects' => $subjectsData,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'final_grade' => $this->getLetterGrade($percentage),
                'is_passing' => $percentage >= 50
            ];
        }

        $pdf = Pdf::loadView('panels.admin.reports.report-cards.pdf', [
            'studentsData' => $studentData,
            'section' => $section,
            'academicYear' => $academicYear,
            'certificateType' => $request->certificate_type,
            'semester' => Semester::find($request->semester_id)
        ], [], [
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'format' => 'A4',
            'orientation' => 'P'
        ]);
        
        $filename = 'شهادات_' . str_replace(' ', '_', $section->schoolClass->name . '_' . $section->name) . '.pdf';
        
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
