@extends('layouts.app')
@section('title', 'لوحة تقارير الحضور')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-sms-primary">لوحة تقارير الحضور</h2>
            <p class="text-sms-muted mb-0">نظرة سريعة على مؤشرات الحضور اليومي عبر المدرسة.</p>
        </div>
        <span class="badge bg-sms-primary text-white fs-6 px-3 py-2">
            <i class="fas fa-calendar-day me-2"></i>{{ \Carbon\Carbon::parse($today)->format('l, F j, Y') }}
        </span>
    </div>

    <x-shared.card class="mb-4 border-0 shadow-sm" shadow="sm">
        <div class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label small text-sms-muted">{{ __('attendance.academic_year') }}</label>
                <form action="{{ route('admin.attendance-reports.dashboard') }}" method="GET">
                    <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('attendance.all_years') }}</option>
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-shared.card>

    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-percent"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ __('attendance.attendance_rate') }}</h3>
                    <div class="number">{{ $todayStats['present_percentage'] ?? 0 }}%</div>
                    <p class="status">تحديث: اليوم</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-9">
            <div class="row g-4">
                @php
                    $statCards = [
                        ['label' => __('attendance.present'), 'key' => 'present_count', 'color' => 'blue', 'icon' => 'check-circle'],
                        ['label' => __('attendance.absent'),  'key' => 'absent_count',  'color' => 'orange',  'icon' => 'times-circle'],
                        ['label' => __('attendance.late'),    'key' => 'late_count',    'color' => 'green', 'icon' => 'clock'],
                    ];
                @endphp
                @foreach($statCards as $sc)
                    <div class="col-12 col-sm-4">
                        <div class="stat-card {{ $sc['color'] }}">
                            <div class="stat-icon">
                                <i class="fas fa-{{ $sc['icon'] }}"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $sc['label'] }}</h3>
                                <div class="number">{{ $todayStats[$sc['key']] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <x-shared.card class="h-100 border-0" shadow="sm">
                <x-slot:header>
                    <h6 class="m-0 fw-semibold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>الشعب التي تحتاج متابعة</h6>
                </x-slot:header>
                <ul class="list-group list-group-flush">
                    @forelse($lowestSections as $rank)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-0">{{ $rank['section']->grade?->name ?? '' }} — {{ $rank['section']->name ?? '' }}</h6>
                                <small class="text-sms-muted">{{ $rank['present'] }} / {{ $rank['total'] }} حاضر</small>
                            </div>
                            <span class="badge bg-{{ $rank['rate'] >= 75 ? 'success' : ($rank['rate'] >= 50 ? 'warning' : 'danger') }} rounded-pill fs-6">
                                {{ $rank['rate'] }}%
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-sms-muted py-4">لا توجد بيانات جلسات.</li>
                    @endforelse
                </ul>
            </x-shared.card>
        </div>

        <div class="col-md-6">
            <x-shared.card class="h-100 border-0" shadow="sm">
                <x-slot:header>
                    <h6 class="m-0 fw-semibold"><i class="fas fa-user-times text-danger me-2"></i>أعلى الغائبين (تراكمي)</h6>
                </x-slot:header>
                <ul class="list-group list-group-flush">
                    @forelse($topAbsentees as $absentee)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($absentee['student']->photo)
                                    <img src="{{ asset('storage/'.$absentee['student']->photo) }}" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-sms-secondary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:16px;">
                                        {{ strtoupper(substr($absentee['student']->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $absentee['student']->name }}</h6>
                                    <small class="text-sms-muted">{{ $absentee['student']->section?->grade?->name ?? '' }} — {{ $absentee['student']->section?->name ?? '' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-danger">{{ $absentee['absent_count'] }}</div>
                                <small class="text-sms-muted d-block">غياب</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-sms-muted py-4">لا توجد سجلات غياب.</li>
                    @endforelse
                </ul>
            </x-shared.card>
        </div>
    </div>
</div>
@endsection
