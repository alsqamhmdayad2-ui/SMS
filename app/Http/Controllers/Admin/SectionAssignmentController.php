<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class SectionAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('grade')->get();

        $yearId = $request->query('year_id');
        $classId = $request->query('class_id');

        $students = collect();
        $sections = collect();
        
        if ($yearId && $classId) {
            // Find students in this class but without a section
            $students = Student::where('class_id', $classId)
                ->whereNull('section_id')
                ->get();
                
            $sections = Section::where('class_id', $classId)->get();
        }

        return view('panels.admin.section-assignments.index', compact(
            'academicYears', 'classes', 'students', 'sections',
            'yearId', 'classId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'assignments' => 'required|array',
            'assignments.*' => 'required|exists:sections,id',
        ]);

        $yearId = $request->year_id;
        $assignments = $request->assignments;

        DB::beginTransaction();
        try {
            $processedCount = 0;

            foreach ($assignments as $studentId => $sectionId) {
                $student = Student::find($studentId);
                if (!$student) continue;

                // Update the main students table
                $student->update([
                    'section_id' => $sectionId,
                ]);

                // Update the active enrollment record
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $yearId,
                    ],
                    [
                        'grade_id' => $student->grade_id,
                        'class_id' => $student->class_id,
                        'section_id' => $sectionId,
                        'status' => 'active',
                        'registration_date' => now(),
                    ]
                );

                $processedCount++;
            }

            DB::commit();
            return redirect()->route('admin.section-assignments.index')->with('success', "تم توزيع {$processedCount} طالب على الشعب بنجاح.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء التوزيع: ' . $e->getMessage());
        }
    }
}
