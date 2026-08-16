@extends('layouts.app')
@section('title', 'رصد الدرجات')

@push('styles')
<style>
    .assignment-card {
        border: none;
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .assignment-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        color: inherit;
    }
    .assignment-card .card-top {
        background: linear-gradient(135deg, var(--primary, #1e3c72) 0%, var(--secondary, #2a5298) 100%);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .assignment-card .card-top::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 100px; height: 100px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .assignment-card .card-top::after {
        content: '';
        position: absolute;
        bottom: -20px; left: -20px;
        width: 70px; height: 70px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .assignment-card .card-top h5 {
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        margin: 0 0 0.25rem;
        position: relative;
        z-index: 1;
    }
    .assignment-card .card-top .section-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.8rem;
        display: inline-block;
        position: relative;
        z-index: 1;
    }
    .assignment-card .card-top .subject-icon {
        position: absolute;
        left: 1rem; top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        color: rgba(255,255,255,0.15);
        z-index: 0;
    }
    .assignment-card .card-bottom {
        background: white;
        padding: 1rem 1.25rem;
    }
    .progress-thin {
        height: 6px;
        border-radius: 3px;
        background: #f0f4f8;
        overflow: hidden;
    }
    .progress-thin .bar {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #10b981, #34d399);
        transition: width 0.5s ease;
    }
    .locked-ribbon {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(239,68,68,0.9);
        color: white;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        z-index: 2;
    }
    .stat-mini {
        text-align: center;
        flex: 1;
    }
    .stat-mini .val {
        font-size: 1.3rem;
        font-weight: 800;
        display: block;
    }
    .stat-mini .lbl {
        font-size: 0.72rem;
        color: #94a3b8;
        display: block;
    }
    .empty-teacher {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
        border-radius: 1.5rem;
        padding: 4rem 2rem;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-sms-primary">
                <i class="fas fa-clipboard-list me-2"></i> رصد الدرجات
            </h2>
            <p class="text-sms-muted mb-0">اختر المادة والشعبة التي تريد رصد درجاتها</p>
        </div>
        @if($currentAcademicYear)
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ $currentAcademicYear->name }}
            </span>
            @if($currentSemester)
            <span class="badge bg-sms-secondary text-white px-3 py-2 fs-6 rounded-pill">
                {{ $currentSemester->name }}
            </span>
            @endif
        </div>
        @endif
    </div>

    <x-alerts />

    {{-- Cards Grid --}}
    @if($cards->isEmpty())
        <div class="empty-teacher">
            <i class="fas fa-chalkboard-teacher fa-4x mb-3 text-primary opacity-25 d-block"></i>
            <h4 class="fw-bold text-dark">لا توجد مواد مسندة إليك</h4>
            <p class="text-muted mb-0">لم يتم إسناد أي حصص إليك في جدول الحصص للعام الدراسي الحالي.<br>تواصل مع الإدارة لتحديث جدولك.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($cards as $card)
            @php
                $semester = $card['semester'];
                $section  = $card['section'];
                $subject  = $card['subject'];
                $url = route('teacher.gradebook.enter', [
                    'section' => $section->id,
                    'subject' => $subject->id,
                    'semester_id' => $semester?->id
                ]);
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ $url }}" class="assignment-card position-relative">
                    @if($card['is_locked'])
                    <span class="locked-ribbon">
                        <i class="fas fa-lock me-1"></i> مقفل
                    </span>
                    @endif

                    <div class="card-top">
                        <i class="fas fa-book-open subject-icon"></i>
                        <h5>{{ $subject->name }}</h5>
                        <span class="section-badge">
                            <i class="fas fa-users me-1"></i>
                            {{ $section->schoolClass?->name ?? '' }} - {{ $section->name }}
                        </span>
                        @if($semester)
                        <div class="mt-2" style="position:relative;z-index:1;">
                            <small class="text-white opacity-75">
                                <i class="fas fa-calendar me-1"></i> {{ $semester->name }}
                            </small>
                        </div>
                        @endif
                    </div>

                    <div class="card-bottom">
                        <div class="d-flex gap-3 mb-3 justify-content-around">
                            <div class="stat-mini">
                                <span class="val text-primary">{{ $card['student_count'] }}</span>
                                <span class="lbl">عدد الطلاب</span>
                            </div>
                            <div class="stat-mini">
                                <span class="val text-success">{{ $card['entered_count'] }}</span>
                                <span class="lbl">تم الرصد</span>
                            </div>
                            <div class="stat-mini">
                                <span class="val {{ $card['completion'] == 100 ? 'text-success' : 'text-warning' }}">{{ $card['completion'] }}%</span>
                                <span class="lbl">الإكمال</span>
                            </div>
                        </div>

                        <div class="progress-thin mb-2">
                            <div class="bar" style="width: {{ $card['completion'] }}%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            @if($card['is_locked'])
                                <small class="text-danger"><i class="fas fa-lock me-1"></i>مقفلة من الإدارة</small>
                            @elseif($card['completion'] == 100)
                                <small class="text-success"><i class="fas fa-check-circle me-1"></i>مكتملة</small>
                            @else
                                <small class="text-muted"><i class="fas fa-pencil-alt me-1"></i>بانتظار الرصد</small>
                            @endif
                            <span class="btn btn-primary btn-sm px-3">
                                <i class="fas fa-arrow-left me-1"></i> ابدأ
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
