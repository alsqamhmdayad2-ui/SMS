<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Grade;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AssessmentComponentController extends Controller
{
    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $grades = Grade::all();
        $subjects = Subject::all(); // Alternatively, filter by Grade if subjects are grade-specific

        $selectedYearId = $request->get('academic_year_id', AcademicYear::where('status', true)->first()->id ?? null);
        $selectedSubjectId = $request->get('subject_id');

        $components = collect();
        $totalWeight = 0;

        if ($selectedYearId && $selectedSubjectId) {
            $components = $this->assessmentService->getSubjectComponents($selectedSubjectId, $selectedYearId);
            $totalWeight = $components->where('status', true)->sum('weight_percentage');
        }

        return view('panels.admin.exams.assessment-components.index', compact(
            'academicYears', 'grades', 'subjects', 'components', 'selectedYearId', 'selectedSubjectId', 'totalWeight'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'weight_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->has('status');
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        
        // Prevent duplicate code for same subject & year
        if (AssessmentComponent::where('academic_year_id', $validated['academic_year_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('code', $validated['code'])
            ->exists()) {
            return redirect()->back()->with('error', 'Component with this Code already exists for the selected subject and year.')->withInput();
        }

        try {
            $this->assessmentService->storeComponent($validated);
            return redirect()->back()->with('success', 'Component added successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function update(Request $request, AssessmentComponent $assessment_component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'boolean',
            // Code is intentionally omitted here based on user feedback to prevent breaking existing calculations
        ]);

        $validated['status'] = $request->has('status');
        $validated['updated_by'] = auth()->id();

        try {
            $this->assessmentService->updateComponent($assessment_component, $validated);
            return redirect()->back()->with('success', 'Component updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(AssessmentComponent $assessment_component)
    {
        // Prevent deletion if used in exam_results
        $isUsed = DB::table('exam_results')
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->where('exams.subject_id', $assessment_component->subject_id)
            ->where('exams.academic_year_id', $assessment_component->academic_year_id)
            ->where('exams.type', strtolower($assessment_component->code))
            ->exists();

        if ($isUsed) {
            return redirect()->back()->with('error', 'Cannot delete this component because it has associated exam results.');
        }

        $assessment_component->delete();
        return redirect()->back()->with('success', 'Component deleted successfully.');
    }

    public function copyFromSubject(Request $request)
    {
        $request->validate([
            'target_academic_year_id' => 'required|exists:academic_years,id',
            'target_subject_id' => 'required|exists:subjects,id',
            'source_subject_id' => 'required|exists:subjects,id|different:target_subject_id',
        ]);

        $sourceComponents = AssessmentComponent::where('academic_year_id', $request->target_academic_year_id)
            ->where('subject_id', $request->source_subject_id)
            ->get();

        if ($sourceComponents->isEmpty()) {
            return redirect()->back()->with('error', 'Source subject has no components.');
        }

        DB::transaction(function () use ($request, $sourceComponents) {
            // Delete existing components if replacing (optional logic based on UI confirm)
            if ($request->has('replace_existing')) {
                 AssessmentComponent::where('academic_year_id', $request->target_academic_year_id)
                    ->where('subject_id', $request->target_subject_id)
                    ->delete();
            }

            foreach ($sourceComponents as $comp) {
                // Check if code exists to avoid duplicates
                $exists = AssessmentComponent::where('academic_year_id', $request->target_academic_year_id)
                    ->where('subject_id', $request->target_subject_id)
                    ->where('code', $comp->code)
                    ->exists();

                if (!$exists) {
                    AssessmentComponent::create([
                        'academic_year_id' => $request->target_academic_year_id,
                        'subject_id' => $request->target_subject_id,
                        'name' => $comp->name,
                        'code' => $comp->code,
                        'weight_percentage' => $comp->weight_percentage,
                        'order' => $comp->order,
                        'status' => $comp->status,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Components copied successfully.');
    }

    public function duplicate(AssessmentComponent $assessment_component)
    {
        try {
            $this->assessmentService->storeComponent([
                'academic_year_id' => $assessment_component->academic_year_id,
                'subject_id' => $assessment_component->subject_id,
                'name' => $assessment_component->name . ' (Copy)',
                'code' => $assessment_component->code . '_COPY',
                'weight_percentage' => 0, // Set to 0 to avoid >100% exception
                'order' => $assessment_component->order + 1,
                'status' => false,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            return redirect()->back()->with('success', 'Component duplicated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to duplicate component.');
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:assessment_components,id',
            'order.*.position' => 'required|integer',
        ]);

        foreach ($validated['order'] as $item) {
            AssessmentComponent::where('id', $item['id'])->update(['order' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }
}
