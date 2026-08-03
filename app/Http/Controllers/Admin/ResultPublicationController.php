<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Subject;
use App\Models\ResultPublication;
use App\Services\ResultPublicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResultPublicationController extends Controller
{
    public function __construct(protected ResultPublicationService $publicationService) {}

    public function index(Request $request)
    {
        $publications = ResultPublication::with(['academicYear', 'semester', 'grade', 'section', 'subject', 'publisher'])
            ->latest()
            ->paginate(20);

        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $grades = Grade::all();
        $subjects = Subject::all();

        return view('panels.admin.exams.result-publications.index', compact(
            'publications', 'academicYears', 'semesters', 'grades', 'subjects'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'grade_id' => 'required|exists:grades,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'published_type' => 'required|in:subject,section,semester',
            'notes' => 'nullable|string'
        ]);

        try {
            $this->publicationService->publish($data, auth()->id());
            return back()->with('success', 'تم نشر النتائج بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while publishing: ' . $e->getMessage())->withInput();
        }
    }

    public function updateStatus(Request $request, ResultPublication $publication)
    {
        $request->validate(['status' => 'required|in:draft,published,archived']);

        if ($request->status === 'draft') {
            $this->publicationService->unpublish($publication);
            return back()->with('success', 'تم إرجاع النشر إلى مسودة.');
        }

        $publication->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث الحالة بنجاح.');
    }

    public function destroy(ResultPublication $publication)
    {
        $this->publicationService->unpublish($publication);
        $publication->delete();
        return back()->with('success', 'تم حذف سجل النشر.');
    }
}
