<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\TeacherService;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $service
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $query = Teacher::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('family_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(20)->withQueryString();
        return view('panels.admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $subjects = \App\Models\Subject::where('status', 1)->get();
        return view('panels.admin.teachers.create', compact('subjects'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'تم إضافة المعلم بنجاح');
    }

    public function show(Teacher $teacher)
    {
        return view('panels.admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $subjects = \App\Models\Subject::where('status', 1)->get();
        return view('panels.admin.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->service->update($teacher, $request->validated());

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'تم تعديل بيانات المعلم بنجاح');
    }

    public function destroy(Teacher $teacher)
    {
        $this->service->delete($teacher);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'تم حذف المعلم بنجاح');
    }
}
