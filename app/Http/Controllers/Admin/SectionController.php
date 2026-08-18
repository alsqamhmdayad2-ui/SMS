<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Services\SectionService;

class SectionController extends Controller
{
    protected $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = $this->sectionService->getAllSections();
        return view('panels.admin.sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::with('grade')->get();
        return view('panels.admin.sections.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request)
    {
        $this->sectionService->createSection($request->validated());
        return redirect()->route('admin.sections.index')->with('success', 'تم إنشاء الشعبة بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        $section->load(['schoolClass.grade', 'students.parent']);
        
        // Get students in the same class but not in this section
        $availableStudents = \App\Models\Student::where('class_id', $section->class_id)
            ->where(function($query) use ($section) {
                $query->whereNull('section_id')
                      ->orWhere('section_id', '!=', $section->id);
            })
            ->get();

        // Get assigned teachers and subjects from timetable
        $assignedTeachers = \App\Models\Timetable::with(['subject', 'teacher'])
            ->where('section_id', $section->id)
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->teacher_id;
            })
            ->values();

        return view('panels.admin.sections.show', compact('section', 'availableStudents', 'assignedTeachers'));
    }

    /**
     * Assign a student to this section
     */
    public function assignStudent(\Illuminate\Http\Request $request, Section $section)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $student = \App\Models\Student::findOrFail($request->student_id);

        // Ensure student belongs to the same class (use loose comparison, IDs may be different types)
        if ((int)$student->class_id !== (int)$section->class_id) {
            return back()->withErrors(['student_id' => 'لا يمكن إضافة طالب من صف مختلف إلى هذه الشعبة.']);
        }

        $student->section_id = $section->id;
        $student->save();

        // Also update enrollment if applicable using DB facade
        \Illuminate\Support\Facades\DB::table('student_enrollments')
            ->where('student_id', $student->id)
            ->where('class_id', $section->class_id)
            ->update(['section_id' => $section->id, 'updated_at' => now()]);

        return back()->with('success', 'تم إضافة الطالب للشعبة بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        $classes = \App\Models\SchoolClass::with('grade')->get();
        return view('panels.admin.sections.edit', compact('section', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSectionRequest $request, Section $section)
    {
        $this->sectionService->updateSection($section, $request->validated());
        return redirect()->route('admin.sections.index')->with('success', 'تم تحديث الشعبة بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        if ($section->students()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الشعبة لوجود طلاب مسجلين بها.');
        }

        $this->sectionService->deleteSection($section);
        return redirect()->route('admin.sections.index')->with('success', 'تم حذف الشعبة بنجاح.');
    }
}
