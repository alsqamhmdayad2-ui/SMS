@extends('layouts.app')
@section('title', 'تفاصيل الشعبة الدراسية')

@section('content')

<x-page-header 
    title="الشعبة الدراسية: {{ $section->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
        <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> تعديل</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الشُعب الدراسية', 'url' => route('admin.sections.index')],
    ['name' => 'تفاصيل الشعبة']
]" />

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:100px;height:100px;font-size:3rem;">
                    <i class="fas fa-users"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $section->name }}</h4>
                <p class="text-muted small mb-3">شعبة دراسية</p>
                <x-status-badge :status="$section->status ? 'active' : 'inactive'" />
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <span class="text-muted"><i class="fas fa-chalkboard me-2"></i> الصف التابع</span>
                    <span class="fw-semibold">{{ $section->schoolClass->name ?? '—' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <span class="text-muted"><i class="fas fa-layer-group me-2"></i> المرحلة الدراسية</span>
                    <span class="fw-semibold">{{ $section->schoolClass->grade->name ?? '—' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <span class="text-muted"><i class="fas fa-user-graduate me-2"></i> الطاقة الاستيعابية</span>
                    <span class="fw-semibold">{{ $section->capacity ?? '—' }} طالب</span>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate text-primary me-2"></i> الطلاب المسجلين</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignStudentModal">
                    <i class="fas fa-user-plus me-1"></i> إضافة طالب للشعبة
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>الاسم</th>
                                <th>الرقم الأكاديمي</th>
                                <th>ولي الأمر</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section->students ?? [] as $student)
                                <tr>
                                    <td class="fw-bold">
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="text-decoration-none">{{ $student->name ?? ($student->first_name . ' ' . $student->family_name) }}</a>
                                    </td>
                                    <td>{{ $student->student_number ?? $student->national_id ?? '—' }}</td>
                                    <td>
                                        @if($student->parent)
                                            <a href="{{ route('admin.parents.show', $student->parent->id) }}" class="text-decoration-none">
                                                {{ $student->parent->full_name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-empty-state 
                                            title="لا يوجد طلاب" 
                                            message="لم يتم تسجيل أي طلاب في هذه الشعبة بعد."
                                            icon="user-graduate" 
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher text-primary me-2"></i> المواد والمعلمون</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>المادة</th>
                                <th>المعلم المسند</th>
                                <th>التخصص</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedTeachers ?? [] as $assignment)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <i class="fas fa-book text-muted me-2"></i>{{ $assignment->subject->name ?? '—' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.teachers.show', $assignment->teacher_id) }}" class="text-decoration-none">
                                            {{ $assignment->teacher->full_name ?? ($assignment->teacher->first_name . ' ' . $assignment->teacher->family_name) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $assignment->teacher->specialization ?? '—' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-empty-state 
                                            title="لا يوجد معلمين" 
                                            message="لم يتم إسناد أي مواد أو معلمين لهذه الشعبة في الجدول الدراسي حتى الآن."
                                            icon="chalkboard-teacher" 
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Student Modal -->
<div class="modal fade" id="assignStudentModal" tabindex="-1" aria-labelledby="assignStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sections.assignStudent', $section->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="assignStudentModalLabel">إضافة طالب مسجل مسبقاً للشعبة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        تُظهر هذه القائمة الطلاب المسجلين في <strong>{{ $section->schoolClass->name ?? 'نفس الصف' }}</strong> فقط وغير المنضمين لهذه الشعبة حالياً.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label required">اختر الطالب</label>
                        <select class="form-select" name="student_id" required>
                            <option value="">-- ابحث واختر طالباً --</option>
                            @forelse($availableStudents ?? [] as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->first_name }} {{ $student->family_name }} ({{ $student->national_id }})
                                </option>
                            @empty
                                <option value="" disabled>لا يوجد طلاب متاحين في هذا الصف غير مسجلين في شعبتك</option>
                            @endforelse
                        </select>
                        @error('student_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة الطالب للشعبة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
