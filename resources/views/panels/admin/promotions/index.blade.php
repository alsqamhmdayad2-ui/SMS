@extends('layouts.app')
@section('title', 'ترقية الطلاب')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-graduation-cap me-2 text-primary"></i>نظام ترقية الطلاب (المرحلة الأولى)</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">ترقية الطلاب</li>
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

<!-- Section 1: Promotion Criteria -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-muted"></i>تحديد مسار الترقية</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.promotions.index') }}" method="GET" id="filter-form">
            <div class="row g-4 align-items-end">
                
                <!-- From Academic Year -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">من العام الدراسي (الحالي)</label>
                    <select name="from_year_id" class="form-select" required>
                        <option value="">اختر العام...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $fromYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->status ? '(نشط)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- From Class -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">من الصف (الحالي)</label>
                    <select name="from_class_id" class="form-select" required>
                        <option value="">اختر الصف...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $fromClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->grade->name ?? '' }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- To Academic Year -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">إلى العام الدراسي (الجديد)</label>
                    <select name="to_year_id" class="form-select border-primary" required>
                        <option value="">اختر العام الجديد...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $toYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- To Class -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">إلى الصف (المستهدف)</label>
                    <select name="to_class_id" class="form-select border-primary" required>
                        <option value="">اختر الصف المستهدف...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $toClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->grade->name ?? '' }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                        <i class="fas fa-search me-2"></i>عرض الطلاب
                    </button>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-light rounded-pill px-4 ms-2 border">إعادة ضبط</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Section 2: Students List for Promotion -->
@if($fromYearId && $toYearId && $fromClassId && $toClassId)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i>قائمة الطلاب للترقية</h5>
            <div class="d-flex gap-3 align-items-center">
                <button type="button" id="btn-mark-all-promoted" class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="fas fa-check-double me-1"></i> تعيين الكل ناجح
                </button>
                <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">عدد الطلاب المتاحين: {{ $students->count() }}</span>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($students->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success opacity-50 mb-3"></i>
                    <h5 class="fw-bold">لا يوجد طلاب متبقين!</h5>
                    <p class="text-muted">إما أن الصف فارغ، أو تم تقييم جميع الطلاب مسبقاً للعام المستهدف.</p>
                </div>
            @else
                <form action="{{ route('admin.promotions.store') }}" method="POST" id="promotion-form">
                    @csrf
                    <input type="hidden" name="from_year_id" value="{{ $fromYearId }}">
                    <input type="hidden" name="to_year_id" value="{{ $toYearId }}">
                    <input type="hidden" name="from_class_id" value="{{ $fromClassId }}">
                    <input type="hidden" name="to_class_id" value="{{ $toClassId }}">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">الطالب</th>
                                    <th>رقم الطالب</th>
                                    <th>الشعبة الحالية</th>
                                    <th style="width: 250px;">قرار نهاية العام (النتيجة)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $student->full_name }}</td>
                                        <td><span class="badge bg-light text-dark font-monospace border">{{ $student->student_number ?? '—' }}</span></td>
                                        <td>
                                            @if($student->section)
                                                <span class="badge bg-info-subtle text-info px-2 py-1">{{ $student->section->name }}</span>
                                            @else
                                                <span class="text-muted small">بدون شعبة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select name="promotions[{{ $student->id }}]" class="form-select form-select-sm" required>
                                                <option value="promoted" class="text-success fw-bold">✅ ناجح (ينتقل للصف الجديد)</option>
                                                <option value="retained" class="text-danger">❌ راسب (يبقى في الصف الحالي)</option>
                                                <option value="graduated" class="text-primary">🎓 متخرج (ينهي دراسته)</option>
                                                <option value="transferred" class="text-warning">🚚 منقول (ينتقل لمدرسة أخرى)</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-white p-4 border-top">
                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <strong>ملاحظة هامة:</strong> الطلاب الناجحون سينتقلون إلى الصف المستهدف بانتظار توزيعهم على الشعب (سيكونون بدون شعبة مؤقتاً).
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm btn-submit">
                                <i class="fas fa-save me-2"></i>اعتماد وحفظ النتائج
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple confirmation before submit
        const form = document.getElementById('promotion-form');
        if(form) {
            form.addEventListener('submit', function(e) {
                if(!confirm('هل أنت متأكد من حفظ نتائج تقييم وترقية الطلاب؟ لا تنسَ القيام بتوزيع الشعب للطلاب الناجحين في المرحلة القادمة.')) {
                    e.preventDefault();
                }
            });
        }

        // Mark All as Promoted button
        const btnMarkAll = document.getElementById('btn-mark-all-promoted');
        if(btnMarkAll) {
            btnMarkAll.addEventListener('click', function() {
                const selects = document.querySelectorAll('select[name^="promotions["]');
                selects.forEach(select => {
                    select.value = 'promoted';
                    
                    // Visual feedback
                    select.classList.add('bg-success-subtle');
                    setTimeout(() => { select.classList.remove('bg-success-subtle'); }, 500);
                });
            });
        }
    });
</script>
@endpush
