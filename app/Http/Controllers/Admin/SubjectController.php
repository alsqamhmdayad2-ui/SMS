<?php

/*
 * CONFLICTS FOUND AND FIXED:
 *
 * 1. [BUG] update() didn't sync subject_section_teacher when classes were added/removed
 * 2. [BUG] SectionController::assignStudent used !== (strict) causing type mismatch
 * 3. [CLEANUP] Orphaned grade_id/class_id columns in subjects table (migration dropped below)
 * 4. [CLEANUP] grade_id validation was required but only used client-side for JS filtering
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Teacher;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Services\SubjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    protected $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    public function index()
    {
        $search = request('search');
        $subjects = $this->subjectService->getAllSubjects($search);
        return view('panels.admin.subjects.index', compact('subjects', 'search'));
    }

    public function create()
    {
        $grades = \App\Models\Grade::where('status', true)->get();
        $classes = SchoolClass::where('status', 1)->get();
        return view('panels.admin.subjects.create', compact('grades', 'classes'));
    }

    public function store(StoreSubjectRequest $request)
    {
        $data = $request->validated();
        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']); // grade_id is not in fillable anyway

        $subject = Subject::create($data);

        foreach ($classIds as $classId) {
            $this->linkClassToSubject($subject->id, (int)$classId);
        }

        return redirect()->route('admin.subjects.index')->with('success', 'تم إضافة المادة الدراسية بنجاح.');
    }

    public function show(Subject $subject)
    {
        $subject->load(['sections.schoolClass.grade']);

        // Group sections by their parent class
        $grouped = [];
        foreach ($subject->sections as $section) {
            $classId = $section->class_id;
            if (!isset($grouped[$classId])) {
                $grouped[$classId] = [
                    'class_name' => $section->schoolClass->name ?? '—',
                    'grade_name' => $section->schoolClass->grade->name ?? '—',
                    'sections'   => [],
                ];
            }
            $grouped[$classId]['sections'][] = $section;
        }

        $teachers = $subject->qualifiedTeachers()->orderBy('first_name')->get();
        return view('panels.admin.subjects.show', compact('subject', 'grouped', 'teachers'));
    }

    public function edit(Subject $subject)
    {
        $grades = \App\Models\Grade::where('status', true)->get();
        $classes = SchoolClass::where('status', 1)->get();

        // Derive assigned classes from section assignments (single source of truth)
        $assignedClassIds = DB::table('subject_section_teacher')
            ->join('sections', 'sections.id', '=', 'subject_section_teacher.section_id')
            ->where('subject_section_teacher.subject_id', $subject->id)
            ->pluck('sections.class_id')
            ->unique()
            ->toArray();

        return view('panels.admin.subjects.edit', compact('subject', 'grades', 'classes', 'assignedClassIds'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $data = $request->validated();
        $newClassIds = array_map('intval', $data['class_ids'] ?? []);
        unset($data['class_ids']);

        $subject->update($data);

        // Derive current assigned classes from subject_section_teacher (single source of truth)
        $existingClassIds = DB::table('subject_section_teacher')
            ->join('sections', 'sections.id', '=', 'subject_section_teacher.section_id')
            ->where('subject_section_teacher.subject_id', $subject->id)
            ->pluck('sections.class_id')
            ->unique()
            ->map(fn($id) => (int)$id)
            ->toArray();

        $toAdd    = array_diff($newClassIds, $existingClassIds);
        $toRemove = array_diff($existingClassIds, $newClassIds);

        // Add new class links
        foreach ($toAdd as $classId) {
            $this->linkClassToSubject($subject->id, $classId);
        }

        // Remove unlinked classes (delete section-level rows, preserving others)
        if (!empty($toRemove)) {
            $sectionIds = Section::whereIn('class_id', $toRemove)->pluck('id')->toArray();
            DB::table('subject_section_teacher')
                ->where('subject_id', $subject->id)
                ->whereIn('section_id', $sectionIds)
                ->delete();

            // Also clean class_subject_teacher
            DB::table('class_subject_teacher')
                ->where('subject_id', $subject->id)
                ->whereIn('class_id', $toRemove)
                ->delete();
        }

        return redirect()->route('admin.subjects.index')->with('success', 'تم تعديل المادة الدراسية بنجاح.');
    }

    /**
     * Assign a teacher to a subject for a specific SECTION.
     * This allows: teacher A → Section أ, teacher B → Section ب and ج in the same class.
     */
    public function assignTeacher(Request $request, Subject $subject)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        DB::table('subject_section_teacher')->updateOrInsert(
            ['subject_id' => $subject->id, 'section_id' => $request->section_id],
            ['teacher_id' => $request->teacher_id ?: null, 'updated_at' => now(), 'created_at' => now()]
        );

        if ($request->wantsJson() || $request->ajax()) {
            $teacher = $request->teacher_id ? Teacher::find($request->teacher_id) : null;
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المعلم للشعبة بنجاح.',
                'teacher_name' => $teacher ? $teacher->first_name . ' ' . $teacher->family_name : null
            ]);
        }

        return back()->with('success', 'تم تحديث المعلم للشعبة بنجاح.');
    }

    public function destroy(Subject $subject)
    {
        $this->subjectService->deleteSubject($subject);
        return redirect()->route('admin.subjects.index')->with('success', 'تم حذف المادة الدراسية بنجاح.');
    }

    /**
     * Helper: Link a subject to a class by registering its sections.
     * Uses subject_section_teacher as the single source of truth.
     */
    private function linkClassToSubject(int $subjectId, int $classId): void
    {
        // Keep class_subject_teacher in sync (used for listing in index)
        DB::table('class_subject_teacher')->insertOrIgnore([
            'class_id'   => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Register all sections of this class for section-level teacher assignment
        $sections = Section::where('class_id', $classId)->get();
        foreach ($sections as $section) {
            DB::table('subject_section_teacher')->insertOrIgnore([
                'subject_id' => $subjectId,
                'section_id' => $section->id,
                'teacher_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
