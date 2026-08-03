<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeScale;
use App\Services\GradeScaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GradeScaleController extends Controller
{
    protected GradeScaleService $gradeScaleService;

    public function __construct(GradeScaleService $gradeScaleService)
    {
        $this->gradeScaleService = $gradeScaleService;
    }

    public function index()
    {
        // $this->authorize('viewAny', GradeScale::class); // Enable after policies are fully integrated
        $scales = GradeScale::orderBy('percentage_from', 'desc')->get();
        return view('panels.admin.exams.grade-scales.index', compact('scales'));
    }

    public function store(Request $request)
    {
        // $this->authorize('create', GradeScale::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percentage_from' => 'required|numeric|min:0|max:100',
            'percentage_to' => 'required|numeric|min:0|max:100|gte:percentage_from',
            'letter_grade' => 'required|string|max:10',
            'gpa_point' => 'required|numeric|min:0|max:5',
            'is_passing' => 'boolean',
            'minimum_required_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'boolean',
        ]);

        $validated['is_passing'] = $request->has('is_passing');
        $validated['status'] = $request->has('status');

        try {
            $this->gradeScaleService->storeScale($validated);
            return redirect()->route('admin.grade-scales.index')->with('success', 'تم إنشاء مقياس التقييم بنجاح.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function update(Request $request, GradeScale $gradeScale)
    {
        // $this->authorize('update', $gradeScale);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percentage_from' => 'required|numeric|min:0|max:100',
            'percentage_to' => 'required|numeric|min:0|max:100|gte:percentage_from',
            'letter_grade' => 'required|string|max:10',
            'gpa_point' => 'required|numeric|min:0|max:5',
            'is_passing' => 'boolean',
            'minimum_required_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'boolean',
        ]);

        $validated['is_passing'] = $request->has('is_passing');
        $validated['status'] = $request->has('status');

        try {
            $this->gradeScaleService->updateScale($gradeScale, $validated);
            return redirect()->route('admin.grade-scales.index')->with('success', 'تم تحديث مقياس التقييم بنجاح.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(GradeScale $gradeScale)
    {
        // $this->authorize('delete', $gradeScale);

        // Prevent deletion of Default Scale (as per user request)
        if (strtolower($gradeScale->name) === 'default scale') {
            return redirect()->back()->with('error', 'The default scale cannot be deleted. You can disable it instead.');
        }

        // Ideally, check if used in StudentSubjectGrade before deleting.
        if (\App\Models\StudentSubjectGrade::where('letter_grade', $gradeScale->letter_grade)->exists()) {
             return redirect()->back()->with('error', 'Cannot delete this scale because it is already used in student results. Consider disabling it instead.');
        }

        $gradeScale->delete();
        return redirect()->route('admin.grade-scales.index')->with('success', 'تم حذف مقياس التقييم بنجاح.');
    }
}
