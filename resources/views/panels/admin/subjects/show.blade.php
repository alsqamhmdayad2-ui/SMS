@extends('layouts.app')
@section('title', 'تفاصيل المادة: ' . $subject->name)

@section('content')

<x-page-header title="مادة: {{ $subject->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
        <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> تعديل</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المواد الدراسية', 'url' => route('admin.subjects.index')],
    ['name' => $subject->name]
]" />

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Subject Info Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:85px;height:85px;font-size:2.2rem;">
                    <i class="fas fa-book"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $subject->name }}</h5>
                <p class="text-muted small font-monospace mb-3">{{ $subject->code }}</p>
                <x-status-badge :status="$subject->status ? 'active' : 'inactive'" />
            </div>
            @if($subject->description)
            <div class="card-footer bg-light text-muted small p-3 text-center">
                {{ $subject->description }}
            </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">إجمالي الصفوف</span>
                    <span class="badge bg-primary">{{ count($grouped) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">إجمالي الشعب</span>
                    <span class="badge bg-info">{{ $subject->sections->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">شعب بدون معلم</span>
                    @php $noTeacher = $subject->sections->filter(fn($s) => !$s->pivot->teacher_id)->count(); @endphp
                    <span class="badge {{ $noTeacher > 0 ? 'bg-warning text-dark' : 'bg-success' }}">{{ $noTeacher }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Assignment per Section -->
    <div class="col-md-9">
        @if(empty($grouped))
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-chalkboard fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">لم يتم ربط هذه المادة بأي صف بعد.<br>
                        <a href="{{ route('admin.subjects.edit', $subject->id) }}">اضغط هنا لربطها بالصفوف</a>
                    </p>
                </div>
            </div>
        @else
            @foreach($grouped as $classId => $classData)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex align-items-center py-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary me-2">{{ $classData['grade_name'] }}</span>
                        <strong>{{ $classData['class_name'] }}</strong>
                    </div>
                    <span class="ms-auto text-muted small">{{ count($classData['sections']) }} شعب</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>الشعبة</th>
                                <th>المعلم الحالي</th>
                                <th style="width:320px;">تعيين / تغيير المعلم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classData['sections'] as $section)
                            <tr>
                                <td>
                                    <span class="fw-semibold"><i class="fas fa-door-open text-muted me-1"></i>{{ $section->name }}</span>
                                </td>
                                <td>
                                    @if($section->pivot->teacher_id)
                                        @php $t = $teachers->find($section->pivot->teacher_id) @endphp
                                        <span class="text-success fw-semibold">
                                            <i class="fas fa-user-tie me-1"></i>
                                            {{ $t ? $t->first_name.' '.$t->family_name : '—' }}
                                        </span>
                                    @else
                                        <span class="text-warning fst-italic">
                                            <i class="fas fa-exclamation-triangle me-1"></i>غير معين
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.subjects.assignTeacher', $subject->id) }}" method="POST" class="d-flex gap-2 assign-teacher-form">
                                        @csrf
                                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                                        <select name="teacher_id" 
                                                class="form-select form-select-sm searchable-select"
                                                data-placeholder="-- اختر معلماً --"
                                                style="width:100%;">
                                            <option value="">-- بدون معلم --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $section->pivot->teacher_id == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->first_name }} {{ $teacher->family_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary px-3 submit-btn">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @endif
    </div>
@push('styles')
<style>
#assign-toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-20px);
    z-index: 9999;
    opacity: 0;
    transition: all 0.3s ease;
    pointer-events: none;
    min-width: 280px;
    text-align: center;
}
#assign-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
</style>
@endpush

@push('scripts')
<script>
// Inline toast (no SweetAlert, no popup)
function showInlineToast(message, type = 'success') {
    let toast = document.getElementById('assign-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'assign-toast';
        document.body.appendChild(toast);
    }
    const color = type === 'success' ? 'bg-success' : 'bg-danger';
    toast.className = `alert ${color} text-white shadow py-2 px-4 rounded-3`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
        toast.classList.remove('show');
    }, 2500);
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.assign-teacher-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('.submit-btn');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const row = this.closest('tr');
                    const teacherCell = row.querySelectorAll('td')[1];
                    if (data.teacher_name) {
                        teacherCell.innerHTML = `<span class="text-success fw-semibold"><i class="fas fa-user-tie me-1"></i>${data.teacher_name}</span>`;
                    } else {
                        teacherCell.innerHTML = `<span class="text-warning fst-italic"><i class="fas fa-exclamation-triangle me-1"></i>غير معين</span>`;
                    }
                    showInlineToast(data.message, 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showInlineToast('حدث خطأ أثناء حفظ المعلم.', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalIcon;
                btn.disabled = false;
            });
        });
    });
});
</script>
@endpush

@endsection
