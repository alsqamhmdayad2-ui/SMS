@extends('layouts.app')
@section('title', __('attendance.locked_title'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-shared.card class="text-center mt-5" shadow="sm">
                <div class="py-5">

                    {{-- Lock Icon --}}
                    <div class="mb-4">
                        <div class="rounded-circle bg-sms-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                             style="width:100px;height:100px;">
                            <i class="fas fa-lock fa-3x text-sms-secondary"></i>
                        </div>
                    </div>

                    <h3 class="fw-bold text-sms-secondary mb-1">{{ __('attendance.locked_title') }}</h3>
                    <p class="text-sms-muted mb-4">هذه الجلسة مغلقة ولا يمكن تعديلها حالياً.</p>

                    {{-- Session Info --}}
                    <div class="bg-sms-light rounded p-3 text-start mb-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-sms-muted d-block">{{ __('attendance.subject') }}</small>
                                <strong>{{ $timetable->subject->name ?? '—' }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-sms-muted d-block">{{ __('attendance.section') }}</small>
                                <strong>{{ $session->section->name ?? '—' }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-sms-muted d-block">{{ __('attendance.date') }}</small>
                                <strong>{{ $session->date?->format('D, F j, Y') ?? '—' }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-sms-muted d-block">{{ __('attendance.period') }}</small>
                                <strong>{{ __('attendance.period') }} {{ $session->period_number }}</strong>
                            </div>
                            @if($session->locked_at)
                            <div class="col-6">
                                <small class="text-sms-muted d-block">Locked At</small>
                                <strong>{{ $session->locked_at?->format('h:i A') ?? '—' }}</strong>
                            </div>
                            @endif
                            @if($session->lockedBy)
                            <div class="col-6">
                                <small class="text-sms-muted d-block">Locked By</small>
                                <strong>{{ $session->lockedBy->name }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Attendance Summary --}}
                    @php
                        $recs    = $session->records;
                        $total   = $recs->count();
                        $present = $recs->where('status', 'present')->count();
                        $absent  = $recs->where('status', 'absent')->count();
                        $late    = $recs->where('status', 'late')->count();
                    @endphp
                    <div class="d-flex justify-content-center gap-4 mb-4">
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-sms-success">{{ $present }}</div>
                            <small class="text-sms-muted">{{ __('attendance.present') }}</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-sms-danger">{{ $absent }}</div>
                            <small class="text-sms-muted">{{ __('attendance.absent') }}</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-warning">{{ $late }}</div>
                            <small class="text-sms-muted">{{ __('attendance.late') }}</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-sms-secondary">{{ $total }}</div>
                            <small class="text-sms-muted">{{ __('attendance.total') }}</small>
                        </div>
                    </div>

                    <p class="text-sms-muted small mb-4">
                        يرجى التواصل مع الإدارة إذا كنت بحاجة إلى إلغاء إغلاق هذه الجلسة.
                    </p>

                    <a href="{{ route('teacher.attendance.today') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> {{ __('attendance.back_to_classes') }}
                    </a>
                </div>
            </x-shared.card>
        </div>
    </div>
</div>
@endsection
