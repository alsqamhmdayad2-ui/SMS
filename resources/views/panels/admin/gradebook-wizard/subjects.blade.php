@extends('layouts.app')

@section('title', 'رصد الدرجات - اختيار المادة')

@push('styles')
<style>
    .wizard-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d6a4f 100%);
        border-radius: 16px; padding: 2rem; color: white; margin-bottom: 2rem;
    }
    .wizard-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
    .wizard-header p  { margin: 0.4rem 0 0; opacity: 0.85; font-size: 0.95rem; }

    .steps-bar {
        display: flex; align-items: center; gap: 0; margin-bottom: 2rem;
        background: white; border-radius: 12px; padding: 1rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .step-item { display: flex; align-items: center; gap: 0.5rem; flex: 1; font-size: 0.88rem; color: #adb5bd; font-weight: 500; }
    .step-item.active { color: #1e3a5f; }
    .step-item.done   { color: #2d6a4f; }
    .step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; background: #e9ecef; color: #6c757d; flex-shrink: 0; }
    .step-item.active .step-num { background: #1e3a5f; color: white; }
    .step-item.done   .step-num { background: #2d6a4f; color: white; }
    .step-divider { width: 40px; height: 2px; background: #dee2e6; margin: 0 0.5rem; flex-shrink: 0; }

    .subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .subject-card {
        background: white; border-radius: 16px; padding: 1.75rem 1.25rem;
        text-align: center; text-decoration: none; color: inherit;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 2px solid transparent; transition: all .25s ease; display: block;
    }
    .subject-card:hover {
        border-color: #6366f1; transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(99,102,241,.15);
        color: #4338ca; text-decoration: none;
    }
    .subject-icon {
        width: 60px; height: 60px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem; font-size: 1.5rem;
    }
    .subject-name { font-size: 1rem; font-weight: 700; margin-bottom: 0.2rem; }
    .subject-code { font-size: 0.78rem; color: #9ca3af; }

    .breadcrumb-nav {
        display: flex; align-items: center; gap: 0.5rem;
        margin-bottom: 1.25rem; font-size: 0.9rem; color: #6c757d;
    }
    .breadcrumb-nav a { color: #1e3a5f; text-decoration: none; font-weight: 500; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #dee2e6; }

    /* Icons palette for subjects */
    .sub-0  { background:#dbeafe; color:#1d4ed8; }
    .sub-1  { background:#dcfce7; color:#15803d; }
    .sub-2  { background:#fef3c7; color:#b45309; }
    .sub-3  { background:#fce7f3; color:#be185d; }
    .sub-4  { background:#ede9fe; color:#6d28d9; }
    .sub-5  { background:#ffedd5; color:#c2410c; }
    .sub-6  { background:#cffafe; color:#0e7490; }
    .sub-7  { background:#fef9c3; color:#a16207; }
    .sub-8  { background:#e0f2fe; color:#0369a1; }
    .sub-9  { background:#f0fdf4; color:#166534; }

    /* Subject icons mapping */
    .icon-math    { content: "📐"; }
    .icon-science { content: "🔬"; }
    .icon-arabic  { content: "📖"; }
    .icon-english { content: "🌍"; }
    .icon-default { content: "📚"; }
</style>
@endpush

@section('content')
<div class="wizard-header">
    <h1><i class="fa-solid fa-pen-to-square me-2"></i>رصد الدرجات</h1>
    <p>
        {{ $schoolClass->grade->name ?? '' }} &nbsp;|&nbsp;
        الصف: <strong>{{ $schoolClass->name }}</strong> &nbsp;|&nbsp;
        الشعبة: <strong>{{ $section->name }}</strong>
    </p>
</div>

<div class="steps-bar">
    <div class="step-item done">
        <span class="step-num"><i class="fa-solid fa-check" style="font-size:.7rem"></i></span>
        اختيار الصف
    </div>
    <div class="step-divider"></div>
    <div class="step-item done">
        <span class="step-num"><i class="fa-solid fa-check" style="font-size:.7rem"></i></span>
        اختيار الشعبة
    </div>
    <div class="step-divider"></div>
    <div class="step-item active">
        <span class="step-num">3</span>
        اختيار المادة
    </div>
    <div class="step-divider"></div>
    <div class="step-item">
        <span class="step-num">4</span>
        رصد الدرجات
    </div>
</div>

<div class="breadcrumb-nav">
    <a href="{{ route('admin.marks.index') }}"><i class="fa-solid fa-house me-1"></i>اختيار الصف</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <a href="{{ route('admin.marks.sections', $schoolClass) }}">{{ $schoolClass->name }}</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <span>شعبة {{ $section->name }}</span>
</div>

<div class="mb-3 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold mb-0"><i class="fa-solid fa-book-open me-2 text-primary"></i>اختر المادة الدراسية</h5>
    <span class="badge bg-light text-dark border">{{ $subjects->count() }} مادة</span>
</div>

@if($subjects->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-book fa-3x opacity-25 mb-3 d-block"></i>
        <p class="fw-semibold">لا توجد مواد مرتبطة بهذه الشعبة</p>
        <p class="small">يرجى ربط المواد بالشعبة أولاً من قسم توزيع المعلمين</p>
        <a href="{{ route('admin.teacher-distributions.index') }}" class="btn btn-primary btn-sm mt-2">
            <i class="fa-solid fa-link me-1"></i>توزيع المعلمين والمواد
        </a>
    </div>
@else
    <div class="subject-grid">
        @foreach($subjects as $index => $subject)
            <a href="{{ route('admin.marks.enter', [$section, $subject]) }}" class="subject-card">
                <div class="subject-icon sub-{{ $index % 10 }}">
                    <i class="fa-solid fa-book"></i>
                </div>
                <div class="subject-name">{{ $subject->name }}</div>
                @if($subject->code)
                    <div class="subject-code">{{ $subject->code }}</div>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
