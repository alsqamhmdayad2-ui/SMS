@extends('layouts.app')
@section('title', 'الخطة الدراسية - حسب المادة')

@section('content')

<x-page-header title="الخطة الدراسية (حسب المادة)">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المناهج', 'url' => '#'],
    ['name' => 'الخطة الدراسية', 'url' => route('admin.study-plans.index')],
    ['name' => 'حسب المادة'],
]" />

{{-- Tabs --}}
<div class="mb-4">
    <div class="nav nav-pills gap-2">
        <a href="{{ route('admin.study-plans.index') }}" class="nav-link border">
            <i class="fas fa-school me-1"></i> حسب الصف
        </a>
        <a href="{{ route('admin.study-plans.by-subject') }}" class="nav-link bg-primary text-white active">
            <i class="fas fa-book me-1"></i> حسب المادة
        </a>
    </div>
</div>

{{-- Subject selector --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.study-plans.by-subject') }}" id="subjectForm">
            <div class="row align-items-end g-3">
                <div class="col-md-5">
                    <label for="subject_id" class="form-label small fw-bold">اختر المادة الدراسية <span class="text-danger">*</span></label>
                    <select class="form-select" name="subject_id" id="subject_id" onchange="document.getElementById('subjectForm').submit()">
                        <option value="">-- يرجى اختيار المادة --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ ($selectedSubject && $selectedSubject->id == $subject->id) ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedSubject)
                <div class="col-md-4">
                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        أنت تعدّل حصص مادة <strong>{{ $selectedSubject->name }}</strong> لجميع الصفوف المرتبطة بها دفعة واحدة.
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedSubject)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-book me-2"></i> {{ $selectedSubject->name }} - عدد الحصص لكل صف</h6>
                <small class="text-muted">عدل عدد الحصص الأسبوعية لكل صف ثم اضغط حفظ</small>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2">
                {{ $classPlans->count() }} صف مرتبط
            </span>
        </div>
        <div class="card-body p-4">
            @if($classPlans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-unlink fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">لم يتم ربط هذه المادة بأي صف بعد.</p>
                    <a href="{{ route('admin.study-plans.index') }}" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="fas fa-link me-1"></i> ربط المادة بصف
                    </a>
                </div>
            @else
                <form action="{{ route('admin.study-plans.save-by-subject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%;">المرحلة الدراسية</th>
                                    <th style="width: 35%;">الصف</th>
                                    <th>عدد الحصص الأسبوعية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classPlans as $plan)
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-layer-group me-1 text-muted"></i>
                                            {{ $plan->grade_name }}
                                        </td>
                                        <td class="fw-bold text-start">
                                            <i class="fas fa-door-open text-muted me-2"></i>
                                            {{ $plan->class_name }}
                                        </td>
                                        <td>
                                            <div class="input-group mx-auto" style="max-width: 150px;">
                                                <input type="text"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       class="form-control text-center fw-bold numeric-input"
                                                       name="weekly_periods[{{ $plan->class_id }}]"
                                                       value="{{ $plan->weekly_periods ?? 0 }}"
                                                       required>
                                                <span class="input-group-text">حصة</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-start text-primary">
                                        <i class="fas fa-calculator me-2"></i> متوسط الحصص الأسبوعية
                                    </td>
                                    <td>
                                        <span id="avg-periods" class="fs-5">0</span>
                                        <span class="text-muted small"> حصة / صف</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.study-plans.by-subject') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i> حفظ جميع الصفوف
                        </button>
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
        const numericInputs = document.querySelectorAll('.numeric-input');
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const avgEl = document.getElementById('avg-periods');

        function calculateAvg() {
            let total = 0;
            let count = numericInputs.length;
            numericInputs.forEach(input => {
                total += parseInt(input.value || 0, 10);
            });
            avgEl.textContent = count > 0 ? (total / count).toFixed(1) : 0;
        }

        numericInputs.forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value;
                for (let i = 0; i < 10; i++) {
                    const regex = new RegExp(arabicNumbers[i], 'g');
                    value = value.replace(regex, i);
                }
                this.value = value.replace(/[^0-9]/g, '');
                calculateAvg();
            });
        });

        calculateAvg();
    });
</script>
@endpush
