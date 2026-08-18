<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\ParentModel;
use App\Services\StudentRegistrationService;
use App\Services\StudentService;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $service,
        protected StudentRegistrationService $registrationService
    ) {}

    public function index(Request $request)
    {
        $query = Student::with(['grade', 'schoolClass', 'section'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('family_name', 'like', "%{$search}%");
                  
                if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                    $q->orWhereRaw("(first_name || ' ' || father_name || ' ' || grandfather_name || ' ' || family_name) LIKE ?", ["%{$search}%"]);
                } else {
                    $q->orWhereRaw("CONCAT(first_name, ' ', father_name, ' ', grandfather_name, ' ', family_name) LIKE ?", ["%{$search}%"]);
                }
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $students = $query->paginate(20)->withQueryString();
        $classes = \App\Models\SchoolClass::with('sections.schoolClass.grade')->get();
        return view('panels.admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $parents = ParentModel::all();
        $grades = \App\Models\Grade::with('classes.sections')->get();

        return view('panels.admin.students.create', compact('parents', 'grades'));
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        
        // Handle Parent Logic
        $parentId = $data['parent_id'] ?? null;
        if (!$parentId) {
            $parent = ParentModel::create([
                'first_name' => $data['parent_first_name'],
                'father_name' => $data['parent_father_name'],
                'grandfather_name' => $data['parent_grandfather_name'],
                'family_name' => $data['parent_family_name'],
                'national_id' => $data['parent_national_id'],
                'guardian_type' => $data['guardian_type'],
                'phone_1' => $data['parent_phone_1'],
                'phone_2' => $data['parent_phone_2'] ?? null,
                'occupation' => $data['parent_occupation'] ?? null,
                'workplace' => $data['parent_workplace'] ?? null,
                'address' => $data['parent_address'] ?? ($data['governorate'] . ' - ' . $data['city'] . ' - ' . ($data['street'] ?? '')),
            ]);
            $parentId = $parent->id;
        }

        // Student Data
        $studentData = [
            'first_name'       => $data['first_name'],
            'father_name'      => $data['father_name'],
            'grandfather_name' => $data['grandfather_name'],
            'family_name'      => $data['family_name'],
            'english_name'     => $data['english_name'] ?? null,
            'national_id'      => $data['national_id'],
            'phone'            => $data['phone'] ?? null,
            'birth_date'       => $data['birth_date'],
            'place_of_birth'   => $data['place_of_birth'] ?? null,
            'gender'           => $data['gender'],
            'nationality'      => $data['nationality'] ?? 'فلسطيني',
            'blood_type'       => $data['blood_type'] ?? null,
            'religion'         => $data['religion'] ?? null,
            'health_status'    => $data['health_status'] ?? null,
            'governorate'      => $data['governorate'],
            'city'             => $data['city'],
            'region'           => $data['region'] ?? null,
            'neighborhood'     => $data['neighborhood'] ?? null,
            'street'           => $data['street'] ?? null,
            'nearest_landmark' => $data['nearest_landmark'] ?? null,
            'address'          => implode(' - ', array_filter([$data['governorate'], $data['city'], $data['street'] ?? ''])),
        ];

        // Handle avatar upload
        if (request()->hasFile('avatar')) {
            $studentData['avatar'] = request()->file('avatar')->store('avatars/students', 'public');
        }

        // Enrollment Data
        $enrollmentData = [
            'academic_year_id' => 1, // Default or fetch active
            'stage_id' => $data['stage_id'],
            'grade_id' => $data['grade_id'],
            'section_id' => $data['section_id'],
            'registration_date' => $data['registration_date'],
            'registration_type' => $data['registration_type'],
            'previous_school' => $data['previous_school'] ?? null,
            'transfer_reason' => $data['transfer_reason'] ?? null,
        ];

        $this->registrationService->registerStudent($studentData, $enrollmentData, $parentId);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function show(Student $student)
    {
        $student->load(['grade', 'schoolClass', 'section', 'parent']);
        // Only load sections that belong to the student's current class
        $sections = \App\Models\Section::where('class_id', $student->class_id)
                                       ->where('status', 1)->get();
        
        return view('panels.admin.students.show', compact('student', 'sections'));
    }

    public function transfer(\Illuminate\Http\Request $request, Student $student)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        // Ensure the new section belongs to the same class
        $newSection = \App\Models\Section::findOrFail($request->section_id);
        if ($newSection->class_id != $student->class_id) {
            return back()->withErrors(['section_id' => 'الشعبة الجديدة لا تنتمي لنفس الصف الدراسي.']);
        }

        $student->update([
            'section_id' => $request->section_id,
        ]);

        \Illuminate\Support\Facades\DB::table('student_enrollments')
            ->where('student_id', $student->id)
            ->update([
                'section_id' => $request->section_id,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'تم تغيير شعبة الطالب بنجاح.');
    }

    public function edit(Student $student)
    {
        $parents = ParentModel::all();
        $grades = \App\Models\Grade::with('classes.sections')->get();
        return view('panels.admin.students.edit', compact('student', 'parents', 'grades'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();
        
        // Handle Parent Logic
        $parentId = $data['parent_id'] ?? null;
        if (!$parentId) {
            $parent = ParentModel::create([
                'full_name' => $data['parent_full_name'],
                'national_id' => $data['parent_national_id'],
                'guardian_type' => $data['guardian_type'],
                'phone_1' => $data['parent_phone_1'],
                'phone_2' => $data['parent_phone_2'] ?? null,
                'occupation' => $data['parent_occupation'] ?? null,
                'workplace' => $data['parent_workplace'] ?? null,
                'address' => $data['governorate'] . ' - ' . $data['city'] . ' - ' . $data['street'],
            ]);
            $parentId = $parent->id;
        }

        // Student Data
        $studentData = [
            'first_name'       => $data['first_name'],
            'father_name'      => $data['father_name'],
            'grandfather_name' => $data['grandfather_name'],
            'family_name'      => $data['family_name'],
            'english_name'     => $data['english_name'] ?? null,
            'national_id'      => $data['national_id'],
            'phone'            => $data['phone'] ?? null,
            'birth_date'       => $data['birth_date'],
            'place_of_birth'   => $data['place_of_birth'] ?? null,
            'gender'           => $data['gender'],
            'nationality'      => $data['nationality'] ?? 'فلسطيني',
            'blood_type'       => $data['blood_type'] ?? null,
            'religion'         => $data['religion'] ?? null,
            'health_status'    => $data['health_status'] ?? null,
            'governorate'      => $data['governorate'],
            'city'             => $data['city'],
            'region'           => $data['region'] ?? null,
            'neighborhood'     => $data['neighborhood'] ?? null,
            'street'           => $data['street'] ?? null,
            'nearest_landmark' => $data['nearest_landmark'] ?? null,
            'address'          => implode(' - ', array_filter([$data['governorate'], $data['city'], $data['street'] ?? ''])),
            'parent_id'        => $parentId,
            'grade_id'         => $data['stage_id'],
            'class_id'         => $data['grade_id'],
            'section_id'       => $data['section_id'],
        ];

        // Handle avatar upload — delete old one if replaced
        if (request()->hasFile('avatar')) {
            if ($student->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->avatar);
            }
            $studentData['avatar'] = request()->file('avatar')->store('avatars/students', 'public');
        }

        $student->update($studentData);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم تعديل بيانات الطالب بنجاح');
    }

    public function destroy(Student $student)
    {
        $this->service->delete($student);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم حذف الطالب بنجاح');
    }
}
