@extends('layouts.app')
@section('title', 'توزيع المعلمين ')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>توزيع المعلمين</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">توزيع المعلمين</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-muted"></i>تحديد الصف الدراسي للتوزيع</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.teacher-distributions.index') }}" method="GET">
            <div class="row g-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary">العام الدراسي:</label>
                    <select name="year_id" class="form-select border-primary" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->status ? '(نشط)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold">اختر الصف الأكاديمي:</label>
                    <select name="class_id" class="form-select border-primary" required>
                        <option value="">اختر الصف...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->grade->name ?? '' }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
                        <i class="fas fa-table me-2"></i>عرض جدول التوزيع
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($classId && $yearId)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-th me-2 text-primary"></i>جدول التوزيع</h5>
            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
                عدد الشعب: {{ $sections->count() }} | عدد المواد: {{ $subjects->count() }}
            </span>
        </div>
        
        <div class="card-body p-0">
            @if($sections->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning opacity-50 mb-3"></i>
                    <h5 class="fw-bold">لا يوجد شعب لهذا الصف!</h5>
                    <p class="text-muted">يجب إضافة شعب لهذا الصف من إدارة الأكاديميات أولاً.</p>
                </div>
            @elseif($subjects->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-book-dead fa-4x text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold">لا يوجد مواد مرتبطة بهذا الصف في هذا العام!</h5>
                    <p class="text-muted">يرجى ربط المواد الدراسية بهذا الصف من شاشة إدارة المواد الدراسية.</p>
                </div>
            @else
                <form action="{{ route('admin.teacher-distributions.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $classId }}">
                    <input type="hidden" name="year_id" value="{{ $yearId }}">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="bg-primary text-white" style="width: 200px;">المادة / الشعبة</th>
                                    @foreach($sections as $section)
                                        <th class="fw-bold fs-6">شعبة ({{ $section->name }})</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td class="fw-bold text-end pe-4 bg-light">
                                            <i class="fas fa-book me-2 text-primary"></i>{{ $subject->name }}
                                        </td>
                                        @foreach($sections as $section)
                                            @php
                                                // Get the currently assigned teacher ID for this cell
                                                $currentTeacherId = $assignments[$subject->id][$section->id] ?? null;
                                            @endphp
                                            <td class="{{ $currentTeacherId ? 'bg-success-subtle' : '' }}">
                                                <select name="assignments[{{ $subject->id }}][{{ $section->id }}]" class="form-select form-select-sm border-0 bg-transparent text-center fw-semibold teacher-select">
                                                    <option value="">-- لم يعين --</option>
                                                    @foreach($teachersBySubject[$subject->id] ?? [] as $teacher)
                                                        <option value="{{ $teacher->id }}" {{ $currentTeacherId == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->first_name }} {{ $teacher->family_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if(empty($teachersBySubject[$subject->id]) || $teachersBySubject[$subject->id]->isEmpty())
                                                    <small class="text-danger d-block mt-1" style="font-size: 10px;">لا يوجد معلمون لهذه المادة</small>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-white p-4 border-top text-end">
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 text-start">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <strong>ملاحظة:</strong> أي تعديل ستقوم به هنا وتضغط حفظ، سينعكس تلقائياً في صفحة كل مادة على حدة. جميع الشاشات متزامنة في قاعدة البيانات.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                            <i class="fas fa-save me-2"></i>حفظ التوزيع الشامل
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

@endsection

@push('scripts')
<style>
    /* Styling for the select cells to look clean without causing layout shifts */
    .teacher-select {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent !important;
    }
    .teacher-select:hover, .teacher-select:focus {
        background-color: #fff !important;
        border: 1px solid #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }
    td.bg-success-subtle {
        background-color: #d1e7dd !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight cell when changed
        const selects = document.querySelectorAll('.teacher-select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                const td = this.closest('td');
                if (this.value) {
                    td.classList.add('bg-success-subtle');
                } else {
                    td.classList.remove('bg-success-subtle');
                }
            });
        });
    });
</script>
@endpush
