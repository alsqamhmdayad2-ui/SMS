@extends('layouts.app')
@section('title', 'توزيع الطلاب على الشعب')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-sitemap me-2 text-primary"></i>توزيع الشعب (المرحلة الثانية)</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">توزيع الطلاب على الشعب</li>
            </ol>
        </nav>
    </div>
</div>

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

<!-- Section 1: Selection Criteria -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-muted"></i>تحديد الدفعة غير الموزعة</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.section-assignments.index') }}" method="GET" id="filter-form">
            <div class="row g-4 align-items-end">
                
                <div class="col-md-5">
                    <label class="form-label fw-semibold">العام الدراسي المستهدف</label>
                    <select name="year_id" class="form-select" required>
                        <option value="">اختر العام...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->status ? '(نشط)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label fw-semibold">الصف المستهدف</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">اختر الصف...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->grade->name ?? '' }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
                        <i class="fas fa-search me-2"></i>عرض
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Section 2: Assignment Table -->
@if($yearId && $classId)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-1 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i>قائمة الطلاب غير الموزعين</h5>
                <span class="text-muted small">طلاب تم ترقيتهم للصف لكن بدون شعبة</span>
            </div>
            
            @if($students->isNotEmpty() && $sections->isNotEmpty())
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="btn-auto-assign">
                    <i class="fas fa-random me-2"></i>توزيع عشوائي عادل
                </button>
            </div>
            @endif
        </div>
        
        <div class="card-body p-0">
            @if($sections->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning opacity-50 mb-3"></i>
                    <h5 class="fw-bold">لا يوجد شعب لهذا الصف!</h5>
                    <p class="text-muted">يرجى إضافة شعب دراسية لهذا الصف من شاشة إدارة الصفوف والشعب قبل المتابعة.</p>
                </div>
            @elseif($students->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success opacity-50 mb-3"></i>
                    <h5 class="fw-bold">اكتمل التوزيع</h5>
                    <p class="text-muted">جميع الطلاب المسجلين في هذا الصف تم تعيين شعب لهم.</p>
                </div>
            @else
                <form action="{{ route('admin.section-assignments.store') }}" method="POST" id="assignment-form">
                    @csrf
                    <input type="hidden" name="year_id" value="{{ $yearId }}">
                    <input type="hidden" name="class_id" value="{{ $classId }}">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>اسم الطالب</th>
                                    <th>رقم الطالب</th>
                                    <th style="width: 300px;">الشعبة المعينة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                        <td class="fw-semibold">{{ $student->full_name }}</td>
                                        <td><span class="badge bg-light text-dark border font-monospace">{{ $student->student_number ?? '—' }}</span></td>
                                        <td>
                                            <select name="assignments[{{ $student->id }}]" class="form-select form-select-sm section-dropdown" required>
                                                <option value="">-- يرجى تحديد شعبة --</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}">{{ $section->name }} (سعة: {{ $section->max_students ?? 'غير محدد' }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-white p-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0 py-2">
                                    <i class="fas fa-magic me-3 fa-lg"></i>
                                    <span class="small">استخدم زر <b>التوزيع العشوائي</b> لملء القوائم تلقائياً ثم قم بتعديلها يدوياً إذا لزم الأمر.</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                    <i class="fas fa-save me-2"></i>اعتماد وحفظ التوزيع
                                </button>
                            </div>
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
        const autoAssignBtn = document.getElementById('btn-auto-assign');
        if(autoAssignBtn) {
            autoAssignBtn.addEventListener('click', function() {
                // Get all dropdowns
                const dropdowns = document.querySelectorAll('.section-dropdown');
                if(dropdowns.length === 0) return;
                
                // Get available section IDs from the first dropdown
                const options = Array.from(dropdowns[0].options).filter(opt => opt.value !== '');
                const sectionValues = options.map(opt => opt.value);
                
                if(sectionValues.length === 0) return;

                // Simple round-robin assignment to balance them
                let currentSectionIndex = 0;
                
                // Optional: Randomize the array of dropdowns for randomness
                const shuffledDropdowns = Array.from(dropdowns).sort(() => 0.5 - Math.random());

                shuffledDropdowns.forEach(dropdown => {
                    dropdown.value = sectionValues[currentSectionIndex];
                    
                    // Add a slight visual flash to indicate it changed
                    dropdown.classList.add('bg-success-subtle');
                    setTimeout(() => { dropdown.classList.remove('bg-success-subtle'); }, 500);

                    currentSectionIndex++;
                    if(currentSectionIndex >= sectionValues.length) {
                        currentSectionIndex = 0;
                    }
                });
            });
        }
    });
</script>
@endpush
