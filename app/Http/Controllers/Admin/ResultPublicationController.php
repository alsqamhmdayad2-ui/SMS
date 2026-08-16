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
        $request->validate([
            'publish_scope' => 'required|in:section,grade,school',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'grade_id' => 'required_if:publish_scope,section,grade',
            'section_id' => 'required_if:publish_scope,section',
            'notes' => 'nullable|string'
        ]);

        $scope = $request->publish_scope;
        $sections = collect();

        if ($scope === 'section') {
            $sections = Section::where('id', $request->section_id)->get();
        } elseif ($scope === 'grade') {
            $sections = Section::whereHas('schoolClass', fn($q) => $q->where('grade_id', $request->grade_id))->get();
        } elseif ($scope === 'school') {
            $sections = Section::all();
        }

        if ($sections->isEmpty()) {
            return back()->with('error', 'لم يتم العثور على أي شعب دراسية للاعتماد.');
        }

        $successCount = 0;
        $errors = [];

        foreach ($sections as $section) {
            $data = [
                'academic_year_id' => $request->academic_year_id,
                'semester_id' => $request->semester_id,
                'grade_id' => $section->schoolClass->grade_id ?? $request->grade_id ?? 1,
                'section_id' => $section->id,
                'notes' => $request->notes,
            ];

            try {
                $this->publicationService->publish($data, auth()->id());
                $successCount++;
            } catch (ValidationException $e) {
                // Collect the error message for this specific section
                $sectionName = ($section->schoolClass->name ?? '') . ' - ' . $section->name;
                $errors[] = "الشعبة ($sectionName): " . collect($e->errors())->flatten()->first();
            } catch (\Exception $e) {
                $sectionName = ($section->schoolClass->name ?? '') . ' - ' . $section->name;
                $errors[] = "الشعبة ($sectionName): حدث خطأ غير متوقع.";
            }
        }

        if (count($errors) > 0) {
            $msg = "تم اعتماد $successCount شعبة بنجاح، وتعذر اعتماد " . count($errors) . " شعبة للأسباب التالية:<br>" . implode('<br>', $errors);
            if ($successCount > 0) {
                return back()->with('warning', $msg);
            } else {
                return back()->with('error', $msg);
            }
        }

        return back()->with('success', 'تم اعتماد النتائج وقفل التعديل بنجاح لجميع الشعب المحددة.');
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
