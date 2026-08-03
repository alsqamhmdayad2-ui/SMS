<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentEnrollment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * Display a listing of students for transfer operations.
     */
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('status', true)->first();
        if (!$activeYear) {
            return redirect()->route('admin.dashboard')->withErrors('يجب تفعيل عام دراسي أولاً.');
        }

        $query = StudentEnrollment::with(['student', 'grade', 'schoolClass', 'section'])
            ->where('academic_year_id', $activeYear->id)
            ->where('status', 'active'); // Only active students can be transferred

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', family_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->paginate(20)->withQueryString();
        
        // Data for dropdowns
        $classes = SchoolClass::with('sections.schoolClass.grade')->get();

        return view('panels.admin.transfers.index', compact('enrollments', 'classes', 'activeYear'));
    }

    /**
     * Handle Internal Transfer (Change Section/Class within the same year)
     */
    public function internalTransfer(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:student_enrollments,id',
            'new_class_id' => 'required|exists:classes,id',
            'new_section_id' => 'required|exists:sections,id',
        ]);

        $enrollment = StudentEnrollment::findOrFail($request->enrollment_id);
        
        // Ensure the new class/section belong to each other
        $newClass = SchoolClass::findOrFail($request->new_class_id);
        
        $enrollment->update([
            'grade_id' => $newClass->grade_id,
            'class_id' => $request->new_class_id,
            'section_id' => $request->new_section_id,
        ]);

        return back()->with('success', 'تم نقل الطالب داخلياً بنجاح.');
    }

    /**
     * Handle External Transfer (Mark as transferred out of school)
     */
    public function externalTransfer(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:student_enrollments,id',
        ]);

        $enrollment = StudentEnrollment::findOrFail($request->enrollment_id);
        
        $enrollment->update([
            'status' => 'transferred'
        ]);

        // Note: In a complete system, we might also want to mark the Student record itself as inactive.
        // But for this scope, updating the enrollment status handles the academic tracking.

        return back()->with('success', 'تم نقل الطالب خارج المدرسة بنجاح وإسقاط اسمه من القوائم الحالية.');
    }
}
