<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('grade')->get();

        $fromYearId = $request->query('from_year_id');
        $toYearId = $request->query('to_year_id');
        $fromClassId = $request->query('from_class_id');
        $toClassId = $request->query('to_class_id');

        $students = collect();

        if ($fromYearId && $toYearId && $fromClassId && $toClassId) {
            // Find students currently in the from_class_id
            // In a fully strictly enrolled system, we'd check student_enrollments
            // For now, we rely on the current state in students table to bootstrap
            $students = Student::where('class_id', $fromClassId)
                ->with(['schoolClass', 'section'])
                ->get();
                
            // Filter out students who already have an enrollment for the target year
            // meaning they were already promoted or processed for that year.
            $alreadyProcessedStudentIds = StudentEnrollment::where('academic_year_id', $toYearId)
                ->pluck('student_id')
                ->toArray();
                
            $students = $students->reject(function ($student) use ($alreadyProcessedStudentIds) {
                return in_array($student->id, $alreadyProcessedStudentIds);
            });
        }

        return view('panels.admin.promotions.index', compact(
            'academicYears', 'classes', 'students',
            'fromYearId', 'toYearId', 'fromClassId', 'toClassId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_year_id' => 'required|exists:academic_years,id',
            'to_year_id'   => 'required|exists:academic_years,id|different:from_year_id',
            'from_class_id'=> 'required|exists:classes,id',
            'to_class_id'  => 'required|exists:classes,id',
            'promotions'   => 'required|array',
            'promotions.*' => 'in:promoted,retained,graduated,transferred',
        ]);

        $fromYearId = $request->from_year_id;
        $toYearId = $request->to_year_id;
        $fromClassId = $request->from_class_id;
        $toClassId = $request->to_class_id;
        $promotions = $request->promotions;

        DB::beginTransaction();
        try {
            $processedCount = 0;

            foreach ($promotions as $studentId => $status) {
                $student = Student::find($studentId);
                if (!$student) continue;

                // 1. Archive current year (from_year)
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $fromYearId,
                    ],
                    [
                        'grade_id' => $student->grade_id,
                        'class_id' => $student->class_id,
                        'section_id' => $student->section_id,
                        'status' => $status,
                        'registration_date' => now(),
                    ]
                );

                // 2. Handle the target year based on status
                if ($status === 'promoted' || $status === 'retained') {
                    $newClassId = ($status === 'promoted') ? $toClassId : $fromClassId;
                    $newGradeId = SchoolClass::find($newClassId)->grade_id ?? $student->grade_id;

                    // Update student table to point to the new class, but NO section (Wait for distribution phase)
                    $student->update([
                        'grade_id' => $newGradeId,
                        'class_id' => $newClassId,
                        'section_id' => null, // Distribution will happen later
                    ]);

                    // Create active enrollment for the new year
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $toYearId,
                        ],
                        [
                            'grade_id' => $newGradeId,
                            'class_id' => $newClassId,
                            'section_id' => null,
                            'status' => 'active',
                            'registration_date' => now(),
                        ]
                    );
                } 
                // If graduated or transferred, we don't create an active enrollment for the new year.
                // We could also optionally update their status on the students table to 'inactive' or similar.
                
                $processedCount++;
            }

            DB::commit();
            return redirect()->route('admin.promotions.index')->with('success', "تم تنفيذ قرار الترقية لـ {$processedCount} طالب بنجاح.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء الترقية: ' . $e->getMessage());
        }
    }

    public function management(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('grade')->get();
        
        $yearId = $request->query('year_id');
        $classId = $request->query('class_id');
        $date = $request->query('date');
        
        $enrollments = collect();
        if ($yearId) {
            $query = StudentEnrollment::where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->with(['student', 'schoolClass', 'section', 'grade']);
                
            if ($classId) {
                $query->where('class_id', $classId);
            }
            
            if ($date) {
                // Assuming registration_date is stored as Date or DateTime
                $query->whereDate('registration_date', $date);
            }
                
            $enrollments = $query->latest()->get();
        }

        return view('panels.admin.promotions.management', compact('academicYears', 'classes', 'yearId', 'classId', 'date', 'enrollments'));
    }

    public function undo(Request $request, $enrollmentId)
    {
        DB::beginTransaction();
        try {
            $newEnrollment = StudentEnrollment::findOrFail($enrollmentId);
            $student = Student::find($newEnrollment->student_id);
            
            // Find the previous enrollment that got marked as promoted/retained
            $previousEnrollment = StudentEnrollment::where('student_id', $student->id)
                ->whereIn('status', ['promoted', 'retained'])
                ->latest('id')
                ->first();
                
            if ($previousEnrollment) {
                // Restore student table data to the previous state
                $student->update([
                    'grade_id' => $previousEnrollment->grade_id,
                    'class_id' => $previousEnrollment->class_id,
                    'section_id' => $previousEnrollment->section_id,
                ]);
                
                // Mark previous enrollment back to active
                $previousEnrollment->update(['status' => 'active']);
            }
            
            // Delete the new enrollment
            $newEnrollment->delete();
            
            DB::commit();
            return back()->with('success', 'تم التراجع عن ترقية الطالب بنجاح وإعادته لصفه السابق.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء التراجع: ' . $e->getMessage());
        }
    }

    public function undoAll(Request $request)
    {
        $yearId = $request->input('year_id');
        $classId = $request->input('class_id');
        $date = $request->input('date');

        if (!$yearId) {
            return back()->withErrors('يجب تحديد العام الدراسي على الأقل للتراجع عن الترقية.');
        }

        DB::beginTransaction();
        try {
            $query = StudentEnrollment::where('academic_year_id', $yearId)
                ->where('status', 'active');
                
            if ($classId) {
                $query->where('class_id', $classId);
            }
            if ($date) {
                $query->whereDate('registration_date', $date);
            }

            $enrollments = $query->get();
            $count = 0;

            foreach ($enrollments as $newEnrollment) {
                $student = Student::find($newEnrollment->student_id);
                if (!$student) continue;

                $previousEnrollment = StudentEnrollment::where('student_id', $student->id)
                    ->whereIn('status', ['promoted', 'retained'])
                    ->latest('id')
                    ->first();
                    
                if ($previousEnrollment) {
                    $student->update([
                        'grade_id' => $previousEnrollment->grade_id,
                        'class_id' => $previousEnrollment->class_id,
                        'section_id' => $previousEnrollment->section_id,
                    ]);
                    $previousEnrollment->update(['status' => 'active']);
                }
                
                $newEnrollment->delete();
                $count++;
            }
            
            DB::commit();
            return back()->with('success', "تم التراجع عن ترقية {$count} طالب بنجاح.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء التراجع الجماعي: ' . $e->getMessage());
        }
    }
}
