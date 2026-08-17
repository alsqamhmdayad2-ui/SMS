@extends('layouts.app')
@section('title', __('attendance_reports.daily_report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance_reports.daily_report') }}</h2>
            <p class="text-sms-muted mb-0">Detailed view of all attendance sessions for a specific day.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <x-shared.card class="mb-4 bg-sms-light" shadow="sm">
        <form action="{{ route('admin.attendance-reports.daily') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <x-form.input type="date" name="date" value="{{ $date }}" label="{{ __('attendance_reports.date') }}" required />
            </div>
            <div class="col-md-3">
                <x-form.select name="academic_year_id" label="{{ __('attendance_reports.academic_year') }}">
                    <option value="">All Years</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="view" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i> عرض</button>
                    <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm" title="PDF"><i class="fas fa-file-pdf"></i></button>
                    <button type="submit" name="action" value="excel" class="btn btn-success btn-sm" title="Excel"><i class="fas fa-file-excel"></i></button>
                </div>
            </div>
        </form>
    </x-shared.card>

    @if($data && count($data['sessions']) > 0)
        {{-- Report Content --}}
        <x-shared.card class="mb-4" shadow="sm">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                <div class="rounded-circle bg-sms-success bg-opacity-10 text-sms-success d-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.5rem;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h3>
                    <p class="text-sms-muted mb-0">{{ __('attendance_reports.total_sessions') }}: <strong>{{ count($data['sessions']) }}</strong></p>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-sms-muted small">Generated At</div>
                    <div class="fw-semibold">{{ $data['generated_at'] }}</div>
                </div>
            </div>

            {{-- Overall Stats --}}
            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100 bg-sms-primary text-white" title="{{ __('attendance_reports.avg_attendance') }}" value="{{ $data['stats']['attendance_percentage'] }}%" icon="fas fa-percent" color="light" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.present') }}" value="{{ $data['stats']['present_count'] }}" icon="fas fa-check-circle" color="success" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.absent') }}" value="{{ $data['stats']['absent_count'] }}" icon="fas fa-times-circle" color="danger" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.late') }}" value="{{ $data['stats']['late_count'] }}" icon="fas fa-clock" color="warning" />
                </div>
            </div>

            {{-- All Sessions Table --}}
            <h5 class="fw-bold mb-3 mt-4">All Classes Held Today</h5>
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>{{ __('attendance_reports.period') }}</th>
                    <th>{{ __('attendance_reports.section') }}</th>
                    <th>{{ __('attendance_reports.subject') }}</th>
                    <th>{{ __('attendance_reports.teacher') }}</th>
                    <th class="text-center">{{ __('attendance_reports.attendance_rate') }}</th>
                    <th class="text-center">{{ __('attendance_reports.status') }}</th>
                </x-slot:header>
                <x-slot:body>
                    @foreach($data['sessions'] as $session)
                        <tr>
                            <td>{{ __('attendance_reports.period') }} {{ $session->period_number }}</td>
                            <td>{{ $session->section->schoolClass->name ?? '' }} — {{ $session->section->name ?? '' }}</td>
                            <td>{{ $session->subject->name ?? '—' }}</td>
                            <td>{{ $session->teacher->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-sms-{{ $session->rate >= 75 ? 'success' : ($session->rate >= 50 ? 'warning' : 'danger') }} px-2 py-1 fs-6">
                                    {{ $session->rate }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <x-shared.badge type="{{ $session->status->color() }}">
                                    <i class="fas fa-{{ $session->status === App\Enums\AttendanceSessionStatus::Locked ? 'lock' : 'lock-open' }} me-1"></i>
                                    {{ $session->status->label() }}
                                </x-shared.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table.data-table>

        </x-shared.card>
    @else
        <x-shared.card class="border-0 shadow-sm text-center py-5">
            <i class="fas fa-bed fa-3x mb-3 opacity-25 text-sms-muted"></i>
            <h5 class="text-sms-muted">No attendance sessions recorded for {{ \Carbon\Carbon::parse($date)->format('D, M j') }}</h5>
        </x-shared.card>
    @endif
</div>
@endsection
