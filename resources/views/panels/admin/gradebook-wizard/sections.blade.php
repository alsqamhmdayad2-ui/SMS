@extends('layouts.app')

@section('title', 'رصد الدرجات - اختيار الشعبة')

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

    .steps-bar {
        display: flex; align-items: center; gap: 0;
        margin-bottom: 2rem; background: white;
        border-radius: 12px; padding: 1rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .step-item {
        display: flex; align-items: center; gap: 0.5rem;
        flex: 1; font-size: 0.88rem; color: #adb5bd; font-weight: 500;
    }
    .step-item.active { color: #1e3a5f; }
    .step-item.done   { color: #2d6a4f; }
    .step-num {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.8rem;
        background: #e9ecef; color: #6c757d; flex-shrink: 0;
    }
    .step-item.active .step-num { background: #1e3a5f; color: white; }
    .step-item.done   .step-num { background: #2d6a4f; color: white; }
    .step-divider { width: 40px; height: 2px; background: #dee2e6; margin: 0 0.5rem; flex-shrink: 0; }

    .section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .section-card {
        background: white; border-radius: 16px; padding: 2rem 1.5rem;
        text-align: center; text-decoration: none; color: inherit;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 2px solid transparent; transition: all .25s ease;
        cursor: pointer; display: block;
    }
    .section-card:hover {
        border-color: #2d6a4f; transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(45,106,79,.15);
        color: #2d6a4f; text-decoration: none;
    }
    .section-icon {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem; font-size: 1.8rem;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }
    .section-letter { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem; }
    .section-count  { font-size: 0.85rem; color: #6c757d; }

    .breadcrumb-nav {
        display: flex; align-items: center; gap: 0.5rem;
        margin-bottom: 1.25rem; font-size: 0.9rem; color: #6c757d;
    }
    .breadcrumb-nav a { color: #1e3a5f; text-decoration: none; font-weight: 500; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #dee2e6; }
</style>
@endpush

@section('content')
<div class="wizard-header">
    <h1><i class="fa-solid fa-pen-to-square me-2"></i>رصد الدرجات</h1>
    <p>الصف: <strong>{{ $schoolClass->name }}</strong> &nbsp;|&nbsp; {{ $schoolClass->grade->name ?? '' }}</p>
</div>

<div class="steps-bar">
    <div class="step-item done">
        <span class="step-num"><i class="fa-solid fa-check" style="font-size:.7rem"></i></span>
        اختيار الصف
    </div>
    <div class="step-divider"></div>
    <div class="step-item active">
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

<div class="breadcrumb-nav">
    <a href="{{ route('admin.marks.index') }}"><i class="fa-solid fa-house me-1"></i>اختيار الصف</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <span>{{ $schoolClass->name }}</span>
</div>

<div class="mb-3 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold mb-0"><i class="fa-solid fa-door-open me-2 text-success"></i>اختر الشعبة</h5>
    <span class="badge bg-light text-dark border">{{ $sections->count() }} شعبة</span>
</div>

@if($sections->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-inbox fa-3x opacity-25 mb-3 d-block"></i>
        <p class="fw-semibold">لا توجد شعب لهذا الصف</p>
    </div>
@else
    <div class="section-grid">
        @foreach($sections as $section)
            <a href="{{ route('admin.marks.subjects', $section) }}" class="section-card">
                <div class="section-icon">{{ $section->name }}</div>
                <div class="section-letter">شعبة {{ $section->name }}</div>
                <div class="section-count">
                    <i class="fa-solid fa-users me-1"></i>
                    {{ $section->students->count() }} طالب
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
