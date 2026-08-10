@extends('layouts.app')
@section('title', 'انتهى وقت الاختبار')

@push('styles')
<style>
    .expired-wrapper {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .expired-card {
        background: #fff;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        max-width: 520px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,.1);
    }
    .expired-icon {
        width: 100px; height: 100px; border-radius: 50%;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
    }
    .expired-title { font-size: 1.6rem; font-weight: 800; color: #1e293b; }
    .expired-sub   { color: #64748b; margin-top: .5rem; }
    .expired-info  {
        background: #f8fafc; border-radius: 14px;
        padding: 1.25rem; margin: 1.5rem 0; text-align: right;
    }
    .expired-info p { margin-bottom: .4rem; font-size: .92rem; }
    .expired-info p:last-child { margin-bottom: 0; }
</style>
@endpush

@section('content')
<div class="expired-wrapper">
    <div class="expired-card">
        <div class="expired-icon">
            ⏰
        </div>
        <div class="expired-title">انتهى وقت الاختبار</div>
        <p class="expired-sub">للأسف، انتهى وقت الاختبار ولم يتم حفظ أي إجابة مسبقاً.</p>

        <div class="expired-info">
            <p><i class="fas fa-book text-primary me-1"></i> <strong>الاختبار:</strong> {{ $exam->title }}</p>
            <p><i class="fas fa-clock text-warning me-1"></i> <strong>المدة:</strong> {{ $exam->duration_minutes }} دقيقة</p>
            <p class="text-danger"><i class="fas fa-info-circle me-1"></i>
                لا توجد إجابات محفوظة لهذا الاختبار. يرجى التواصل مع المعلم.
            </p>
        </div>

        <a href="{{ route('student.exams') }}" class="btn btn-primary px-5 rounded-pill">
            <i class="fas fa-arrow-right me-1"></i> العودة إلى الاختبارات
        </a>
    </div>
</div>
@endsection
