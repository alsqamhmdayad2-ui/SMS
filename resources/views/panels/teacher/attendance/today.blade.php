@extends('layouts.app')
@section('title', "حصص اليوم - " . $today->translatedFormat('l، j F Y'))

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance.today_classes') }}</h2>
            <p class="text-sms-muted mb-0">
                <i class="fas fa-calendar-day me-1"></i>
                {{ $today->translatedFormat('l، j F Y') }}
            </p>
        </div>
        <x-shared.badge type="primary" class="fs-6 px-3 py-2">{{ $teacher->name }}</x-shared.badge>
    </div>

    {{-- No classes today --}}
    @if($timetables->isEmpty())
        <x-shared.empty-state
            icon="fas fa-calendar-times"
            title="{{ __('attendance.no_classes_today') }}"
            description="{{ __('attendance.enjoy_day', ['day' => $today->format('l')]) }}"
        />
    @else
        <div class="row g-4">
            @foreach($timetables as $tt)
                @php
                    $session       = $tt->attendance_session;
                    $sessionStatus = $tt->session_status;
                    $isLocked      = $session && $session->isLocked();
                    $isDraft       = $session && $session->isOpen();
                    $isOwned       = $session && $session->teacher_id === $teacher->id;
                    $isFirstPeriod = $tt->is_first_period;
                    $canEdit       = $session ? ($isOwned && $isDraft) : $isFirstPeriod;
                    $isNew         = !$session;
                @endphp

                <div class="col-md-6 col-xl-4">
                    <x-shared.card class="h-100 {{ $isLocked ? 'border-start border-sms-secondary border-4' : ($isDraft ? 'border-start border-warning border-4' : 'border-start border-sms-primary border-4') }}" shadow="sm">

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-clock me-1"></i> {{ __('attendance.period') }} {{ $tt->period_number }}
                                @if($tt->start_time && $tt->end_time)
                                    &nbsp;·&nbsp; {{ $tt->start_time }} – {{ $tt->end_time }}
                                @endif
                            </span>

                            @if($isFirstPeriod)
                                @if($isDraft)
                                    <x-shared.badge type="warning" class="text-dark">
                                        <i class="fas fa-pencil-alt me-1"></i> مسودة (قيد التعديل)
                                    </x-shared.badge>
                                @elseif($isLocked)
                                    <x-shared.badge type="secondary">
                                        <i class="fas fa-lock me-1"></i> {{ __('attendance.locked') }}
                                    </x-shared.badge>
                                @else
                                    <x-shared.badge type="primary">
                                        <i class="fas fa-exclamation-circle me-1"></i> بانتظار التسجيل
                                    </x-shared.badge>
                                @endif
                            @else
                                @if($session)
                                    <x-shared.badge type="success">
                                        <i class="fas fa-check-circle me-1"></i> تم التسجيل
                                    </x-shared.badge>
                                @else
                                    <x-shared.badge type="secondary" class="text-dark">
                                        <i class="fas fa-clock me-1"></i> بانتظار التسجيل من المعلم الأول
                                    </x-shared.badge>
                                @endif
                            @endif
                        </div>

                        {{-- Subject & Section --}}
                        <h4 class="card-title mb-1 fw-bold">{{ $tt->section?->schoolClass?->name ?? '' }} - {{ $tt->section?->name ?? '' }}</h4>
                        <p class="text-sms-muted mb-3">
                            <i class="fas fa-book me-1"></i>
                            أول حصة لك اليوم: {{ $tt->subject->name ?? 'مادة' }} (الحصة {{ $tt->period_number }})
                        </p>

                        {{-- Record Count (if session exists) --}}
                        @if($session)
                            <div class="d-flex gap-3 mb-3 small fw-semibold">
                                <span class="text-sms-success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $session->records->where('status.value', 'present')->count() }} {{ __('attendance.present') }}
                                </span>
                                <span class="text-sms-danger">
                                    <i class="fas fa-times-circle"></i>
                                    {{ $session->records->where('status.value', 'absent')->count() }} {{ __('attendance.absent') }}
                                </span>
                                <span class="text-warning">
                                    <i class="fas fa-clock"></i>
                                    {{ $session->records->where('status.value', 'late')->count() }} {{ __('attendance.late') }}
                                </span>
                            </div>
                        @else
                            <div class="d-flex gap-3 mb-3 small fw-semibold text-sms-muted">
                                <i class="fas fa-info-circle"></i> لم يتم رصد الغياب لهذه الشعبة حتى الآن.
                            </div>
                        @endif

                        {{-- Action Button --}}
                        <div class="mt-auto pt-3">
                            @if($isFirstPeriod)
                                <a href="{{ route('teacher.attendance.take', $tt->id) }}"
                                   class="btn {{ $session ? 'btn-outline-primary' : 'btn-primary' }} w-100">
                                    <i class="fas {{ $session ? 'fa-edit' : 'fa-clipboard-check' }} me-2"></i>
                                    {{ $session ? 'تعديل حضور الشعبة' : 'تسجيل حضور الشعبة' }}
                                </a>
                            @else
                                @if($session)
                                    <a href="{{ route('teacher.attendance.take', $tt->id) }}"
                                       class="btn btn-secondary w-100">
                                        <i class="fas fa-eye me-2"></i> عرض الحضور
                                    </a>
                                @else
                                    <button class="btn btn-light text-sms-muted w-100" disabled>
                                        <i class="fas fa-lock me-2"></i> غير متاح للعرض بعد
                                    </button>
                                @endif
                            @endif
                        </div>
                    </x-shared.card>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
