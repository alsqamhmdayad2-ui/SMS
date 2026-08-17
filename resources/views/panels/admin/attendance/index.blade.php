@extends('layouts.app')
@section('title', __('attendance.sessions'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-sms-primary">إدارة الحضور</h2>
            <p class="text-sms-muted mb-0">عرض الجلسات الحالية، ومتابعة الحالة، وإدارة التعديلات الاستثنائية.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance-sessions.create') }}" class="btn btn-primary px-3">
                <i class="fas fa-plus me-1"></i> فتح جلسة
            </a>
            <span class="badge bg-sms-primary text-white px-3 py-2">
                <i class="fas fa-calendar-check me-2"></i>{{ __('attendance.sessions') }}
            </span>
        </div>
    </div>

    <x-alerts />

    @php
        $pageSessions = collect($sessions->items());
        $totalSessions = $sessions->total();
        $openCount = $pageSessions->where('status.value', 'open')->count();
        $lockedCount = $pageSessions->where('status.value', 'locked')->count();
        $avgAtt = round($pageSessions->avg('attendance_rate') ?? 0);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-primary text-white">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small opacity-75">{{ __('attendance.total_sessions') }}</div>
                            <div class="display-6 fw-bold">{{ $totalSessions }}</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-light">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-sms-muted">{{ __('attendance.open_sessions') }}</div>
                            <div class="display-6 fw-bold text-sms-primary">{{ $openCount }}</div>
                        </div>
                        <i class="fas fa-lock-open fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-light">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-sms-muted">{{ __('attendance.locked_sessions') }}</div>
                            <div class="display-6 fw-bold text-sms-secondary">{{ $lockedCount }}</div>
                        </div>
                        <i class="fas fa-lock fa-2x text-sms-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-light">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-sms-muted">{{ __('attendance.avg_attendance') }}</div>
                            <div class="display-6 fw-bold text-sms-success">{{ $avgAtt }}%</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-sms-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-shared.card class="mb-4 border-0 shadow-sm" shadow="sm">
        <x-slot:header>
            <h6 class="m-0 fw-semibold"><i class="fas fa-filter me-2"></i>{{ __('attendance.filter_sessions') }}</h6>
        </x-slot:header>
        <form action="{{ route('admin.attendance-sessions.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <x-form.select name="academic_year_id" label="{{ __('attendance.academic_year') }}">
                        <option value="">{{ __('attendance.all_years') }}</option>
                        @foreach($filters['academicYears'] as $y)
                            <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-form.select name="semester_id" label="{{ __('attendance.semester') }}">
                        <option value="">{{ __('attendance.all_semesters') }}</option>
                        @foreach($filters['semesters'] as $s)
                            <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-form.select name="section_id" label="{{ __('attendance.section') }}">
                        <option value="">{{ __('attendance.all_sections') }}</option>
                        @foreach($filters['sections'] as $sec)
                            <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->schoolClass?->name ?? '' }} — {{ $sec->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <x-form.select name="status" label="{{ __('attendance.status') }}">
                        <option value="">{{ __('attendance.all_statuses') }}</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>{{ __('attendance.open') }}</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>{{ __('attendance.locked') }}</option>
                    </x-form.select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-form.input type="date" name="date" value="{{ request('date') }}" label="{{ __('attendance.date') }}" />
                </div>
                <div class="col-md-3 col-sm-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> {{ __('attendance.apply') }}</button>
                    <a href="{{ route('admin.attendance-sessions.index') }}" class="btn btn-outline-secondary">{{ __('attendance.reset') }}</a>
                </div>
            </div>
        </form>
    </x-shared.card>

    <x-shared.card shadow="sm" class="border-0">
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>{{ __('attendance.date') }}</th>
                <th>{{ __('attendance.section') }}</th>
                <th>{{ __('attendance.recorded_by') }}</th>
                <th style="width:120px;">{{ __('attendance.attendance_rate') }}</th>
                <th>{{ __('attendance.status') }}</th>
                <th>{{ __('attendance.actions') }}</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->date?->format('D, d M Y') ?? '—' }}</td>
                        <td>{{ $session->section?->schoolClass?->name ?? '' }} — {{ $session->section->name ?? '—' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-sms-light text-sms-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $session->teacher->name ?? '—' }}</div>
                                    @if($session->period_number)
                                        <small class="text-sms-muted">{{ __('attendance.recorded_during_period') }} {{ $session->period_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @php $rate = $session->attendance_rate; @endphp
                            <div class="progress" style="height:8px;" title="{{ $rate }}%">
                                <div class="progress-bar bg-{{ $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}" style="width:{{ $rate }}%"></div>
                            </div>
                            <small class="text-sms-muted">{{ $rate }}%</small>
                        </td>
                        <td>
                            <x-shared.badge :type="$session->status->color()">
                                <i class="fas fa-{{ $session->status === App\Enums\AttendanceSessionStatus::Locked ? 'lock' : 'lock-open' }} me-1"></i>
                                {{ $session->status->label() }}
                            </x-shared.badge>
                        </td>
                        <td>
                            <a href="{{ route('admin.attendance-sessions.show', $session->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> {{ __('attendance.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <x-shared.empty-state icon="calendar-times" title="لا توجد جلسات" message="قم بتعديل الفلاتر للبحث عن الجلسات." />
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
        @if($sessions->hasPages())
            <div class="mt-3">
                {{ $sessions->links() }}
            </div>
        @endif
    </x-shared.card>
</div>
@endsection
