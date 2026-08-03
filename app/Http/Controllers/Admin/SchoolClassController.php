<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Services\SchoolClassService;
use App\Http\Requests\SchoolClass\StoreSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassRequest;

class SchoolClassController extends Controller
{
    public function __construct(
        protected SchoolClassService $service
    ) {}

    public function index()
    {
        $classes = $this->service->getAll();
        return view('panels.admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $grades = Grade::where('status', true)->get();
        $academicYears = \App\Models\AcademicYear::where('status', true)->get();
        return view('panels.admin.classes.create', compact('grades', 'academicYears'));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'تم إضافة الصف بنجاح');
    }

    public function show(SchoolClass $class)
    {
        return view('panels.admin.classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        $grades = Grade::where('status', true)->get();
        $academicYears = \App\Models\AcademicYear::where('status', true)->get();
        return view('panels.admin.classes.edit', compact('class', 'grades', 'academicYears'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class)
    {
        $this->service->update($class, $request->validated());

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'تم تعديل بيانات الصف بنجاح');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->sections()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الصف لاحتوائه على شعب تابعة.');
        }

        $this->service->delete($class);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'تم حذف الصف بنجاح');
    }
}
