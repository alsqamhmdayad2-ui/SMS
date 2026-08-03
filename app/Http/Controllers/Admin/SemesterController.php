<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\AcademicYear;
use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Services\SemesterService;

class SemesterController extends Controller
{
    public function __construct(private SemesterService $service) {}

    public function index()
    {
        $semesters = $this->service->getAll();
        return view('panels.admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $academicYears = AcademicYear::where('status', true)->get();
        return view('panels.admin.semesters.create', compact('academicYears'));
    }

    public function store(StoreSemesterRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('admin.semesters.index')
            ->with('success', 'تم إنشاء الفصل الدراسي بنجاح.');
    }

    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::where('status', true)->get();
        return view('panels.admin.semesters.edit', compact('semester', 'academicYears'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester)
    {
        $this->service->update($semester, $request->validated());
        return redirect()->route('admin.semesters.index')
            ->with('success', 'تم تحديث الفصل الدراسي بنجاح.');
    }

    public function destroy(Semester $semester)
    {
        $this->service->delete($semester);
        return back()->with('success', 'تم حذف الفصل الدراسي بنجاح.');
    }
}
