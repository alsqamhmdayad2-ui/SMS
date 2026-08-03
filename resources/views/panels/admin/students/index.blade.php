@extends('layouts.app')
@section('title', 'قائمة الطلاب')

@section('content')
<div class="page-header">
    <h2>قائمة الطلاب المسجلين</h2>
    <a href="{{ route('admin.students.create') }}" class="btn btn-secondary">
        <i class="fas fa-plus"></i> إضافة طالب جديد
    </a>
</div>

<!-- Filter Area -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">الاسم أو رقم الطالب</label>
                    <input type="text" name="search" class="form-control" placeholder="بحث باسم الطالب أو الرقم..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">الصف</label>
                    <select class="form-select" name="class_id" id="class_select">
                        <option value="">جميع الصفوف</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->grade->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">الشعبة</label>
                    <select class="form-select" name="section_id" id="section_id" {{ request('class_id') ? '' : 'disabled' }}>
                        <option value="">جميع الشعب</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> تصفية النتائج
                    </button>
                    @if(request()->anyFilled(['search', 'class_id', 'section_id']))
                        <a href="{{ route('admin.students.index') }}" class="btn btn-light w-100 border">
                            <i class="fas fa-times"></i> مسح الفلاتر
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table data-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الطالب</th>
                        <th>الاسم الرباعي</th>
                        <th>المرحلة</th>
                        <th>الصف</th>
                        <th>الشعبة</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td><span class="text-muted fw-bold">#{{ $student->student_number ?? $student->national_id }}</span></td>
                            <td>
                                <strong>{{ $student->first_name }} {{ $student->father_name }} {{ $student->grandfather_name }} {{ $student->family_name }}</strong>
                            </td>
                            <td>{{ $student->grade->name ?? '-' }}</td>
                            <td>{{ $student->schoolClass->name ?? '-' }}</td>
                            <td>{{ $student->section->name ?? '-' }}</td>
                            <td>
                                @if($student->status == 'active' || $student->status === null)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2"><i class="fas fa-check-circle me-1"></i> نشط</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2"><i class="fas fa-times-circle me-1"></i> غير نشط</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="action-btns d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-outline-info" title="عرض" aria-label="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل" aria-label="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف" aria-label="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">لا يوجد طلاب مطابقين لبحثك.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classesData = @json($classes->map(function($c) {
        return ['id' => $c->id, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()];
    })->values());

    const selectedSectionId = {{ request('section_id') ? request('section_id') : 'null' }};
    const classSelect = document.getElementById('class_select');
    const sectionSelect = document.getElementById('section_id');

    function filterSections() {
        const classId = classSelect.value;
        sectionSelect.innerHTML = '<option value="">جميع الشعب</option>';

        if (!classId) {
            sectionSelect.disabled = true;
            return;
        }

        sectionSelect.disabled = false;
        const selectedClass = classesData.find(c => c.id == classId);

        if (selectedClass && selectedClass.sections) {
            selectedClass.sections.forEach(section => {
                const isSelected = selectedSectionId == section.id ? 'selected' : '';
                sectionSelect.innerHTML += `<option value="${section.id}" ${isSelected}>${section.name}</option>`;
            });
        }
    }

    classSelect.addEventListener('change', filterSections);

    // Run on load
    if (classSelect.value) {
        filterSections();
    }
});
</script>
@endpush
