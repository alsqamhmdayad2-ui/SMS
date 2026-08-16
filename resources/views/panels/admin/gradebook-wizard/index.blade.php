@extends('layouts.app')

@section('title', 'رصد الدرجات - اختيار الصف')

@push('styles')
<style>
    .wizard-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d6a4f 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .wizard-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
    .wizard-header p  { margin: 0.4rem 0 0; opacity: 0.85; font-size: 0.95rem; }

    /* Breadcrumb steps */
    .steps-bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        font-size: 0.88rem;
        color: #adb5bd;
        font-weight: 500;
    }
    .step-item.active { color: #1e3a5f; }
    .step-item.done   { color: #2d6a4f; }
    .step-num {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.8rem;
        background: #e9ecef; color: #6c757d;
        flex-shrink: 0;
    }
    .step-item.active .step-num { background: #1e3a5f; color: white; }
    .step-item.done   .step-num { background: #2d6a4f; color: white; }
    .step-divider { width: 40px; height: 2px; background: #dee2e6; margin: 0 0.5rem; flex-shrink: 0; }

    /* Class Cards */
    .class-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.25rem;
    }
    .class-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 1.5rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 2px solid transparent;
        transition: all .25s ease;
        cursor: pointer;
        display: block;
    }
    .class-card:hover {
        border-color: #1e3a5f;
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(30,58,95,.15);
        color: #1e3a5f;
        text-decoration: none;
    }
    .class-icon {
        width: 64px; height: 64px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.6rem;
    }
    .class-name  { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.25rem; }
    .class-meta  { font-size: 0.82rem; color: #6c757d; }
    .class-badge {
        display: inline-block;
        background: #e8f4f8;
        color: #1e3a5f;
        border-radius: 20px;
        padding: 0.2rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    .empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; display: block; }

    /* Color palette for classes */
    .color-0 { background: #dbeafe; color: #1e40af; }
    .color-1 { background: #dcfce7; color: #166534; }
    .color-2 { background: #fef3c7; color: #92400e; }
    .color-3 { background: #fce7f3; color: #9d174d; }
    .color-4 { background: #ede9fe; color: #5b21b6; }
    .color-5 { background: #ffedd5; color: #c2410c; }
    .color-6 { background: #cffafe; color: #0e7490; }
    .color-7 { background: #f0fdf4; color: #15803d; }
</style>
@endpush

@section('content')
{{-- ── الهيدر ── --}}
<div class="wizard-header">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h1><i class="fa-solid fa-pen-to-square me-2"></i>رصد الدرجات</h1>
            <p>
                @if($currentAcademicYear)
                    العام الدراسي: <strong>{{ $currentAcademicYear->name }}</strong>
                    @if($currentSemester) &nbsp;|&nbsp; الفصل: <strong>{{ $currentSemester->name }}</strong> @endif
                @else
                    يرجى تفعيل عام دراسي أولاً
                @endif
            </p>
        </div>
    </div>
</div>

{{-- ── شريط الخطوات ── --}}
<div class="steps-bar">
    <div class="step-item active">
        <span class="step-num">1</span>
        اختيار الصف
    </div>
    <div class="step-divider"></div>
    <div class="step-item">
        <span class="step-num">2</span>
        اختيار الشعبة
    </div>
    <div class="step-divider"></div>
    <div class="step-item">
        <span class="step-num">3</span>
        اختيار المادة
    </div>
    <div class="step-divider"></div>
    <div class="step-item">
        <span class="step-num">4</span>
        رصد الدرجات
    </div>
</div>

{{-- ── بطاقات الصفوف ── --}}
<div class="mb-3 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold mb-0"><i class="fa-solid fa-school me-2 text-primary"></i>اختر الصف الدراسي</h5>
    <span class="badge bg-light text-dark border">{{ $classes->count() }} صف</span>
</div>

@if($classes->isEmpty())
    <div class="empty-state">
        <i class="fa-solid fa-inbox"></i>
        <p class="fw-semibold">لا توجد صفوف في هذا العام الدراسي</p>
        <p class="small">يرجى إضافة صفوف أولاً من قسم إدارة الصفوف</p>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-primary mt-2">
            <i class="fa-solid fa-plus me-1"></i>إضافة صف
        </a>
    </div>
@else
    <div class="class-grid">
        @foreach($classes as $index => $class)
            <a href="{{ route('admin.marks.sections', $class) }}" class="class-card">
                <div class="class-icon color-{{ $index % 8 }}">
                    <i class="fa-solid fa-chalkboard-teacher"></i>
                </div>
                <div class="class-name">{{ $class->name }}</div>
                <div class="class-meta">{{ $class->grade->name ?? '' }}</div>
                <span class="class-badge">
                    <i class="fa-solid fa-users me-1"></i>
                    {{ $class->sections->count() }} {{ $class->sections->count() == 1 ? 'شعبة' : 'شعب' }}
                </span>
            </a>
        @endforeach
    </div>
@endif
@endsection
