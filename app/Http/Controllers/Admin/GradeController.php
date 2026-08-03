<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Services\GradeService;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;

class GradeController extends Controller
{
    public function __construct(
        protected GradeService $service
    ) {}

    public function index()
    {
        $grades = $this->service->getAll();
        return view('panels.admin.grades.index', compact('grades'));
    }

    public function create()
    {
        return view('panels.admin.grades.create');
    }

    public function store(StoreGradeRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.grades.index')
            ->with('success', 'تم إضافة المرحلة بنجاح');
    }

    public function show(Grade $grade)
    {
        return view('panels.admin.grades.show', compact('grade'));
    }

    public function edit(Grade $grade)
    {
        return view('panels.admin.grades.edit', compact('grade'));
    }

    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $this->service->update($grade, $request->validated());

        return redirect()
            ->route('admin.grades.index')
            ->with('success', 'تم تعديل بيانات المرحلة بنجاح');
    }

    public function destroy(Grade $grade)
    {
        if ($grade->classes()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المرحلة الدراسية لارتباطها بصفوف.');
        }

        $this->service->delete($grade);

        return redirect()
            ->route('admin.grades.index')
            ->with('success', 'تم حذف المرحلة بنجاح');
    }
}
