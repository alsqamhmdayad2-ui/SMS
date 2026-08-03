<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Services\AcademicYearService;

class AcademicYearController extends Controller
{
    public function __construct(protected AcademicYearService $service) {}

    public function index()
    {
        $academicYears = $this->service->getAll();
        return view('panels.admin.academic_years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('panels.admin.academic_years.create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'تم إنشاء العام الدراسي بنجاح.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('panels.admin.academic_years.edit', compact('academicYear'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $this->service->update($academicYear, $request->validated());

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'تم تحديث العام الدراسي بنجاح.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        try {
            $this->service->delete($academicYear);
            return redirect()->route('admin.academic-years.index')->with('success', 'تم حذف العام الدراسي بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('admin.academic-years.index')->with('error', $e->getMessage());
        }
    }
}
