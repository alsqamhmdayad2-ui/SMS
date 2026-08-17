@extends('layouts.app')
@section('title', __('attendance_reports.teacher_report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance_reports.teacher_report') }}</h2>
            <p class="text-sms-muted mb-0">Overview of a teacher's attendance sessions and locking compliance.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <x-shared.card class="mb-4 bg-sms-light" shadow="sm">
        <form action="{{ route('admin.attendance-reports.teacher') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-form.select name="teacher_id" label="{{ __('attendance_reports.teacher') }}" required>
                    <option value="">-- {{ __('attendance_reports.teacher') }} --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </x-form.select>
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
                <x-form.select name="semester_id" label="Semester">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> {{ __('attendance_reports.filter') }}</button>
            </div>
        </form>
    </x-shared.card>

    @if($data)
        {{-- Report Content --}}
        <x-shared.card class="mb-4" shadow="sm">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                <div class="rounded-circle bg-sms-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.5rem;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold">{{ $data['teacher']->name }}</h3>
                    <p class="text-sms-muted mb-0">{{ __('attendance_reports.student_count') }}: <strong>{{ $data['stats']['total_students'] }}</strong></p>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-sms-muted small">Generated At</div>
                    <div class="fw-semibold">{{ $data['generated_at'] }}</div>
                </div>
            </div>

            {{-- Teacher Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.total_created') }}" value="{{ $data['stats']['total_sessions'] }}" icon="fas fa-calendar-plus" color="primary" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.locked_sessions') }}" value="{{ $data['stats']['locked_sessions'] }}" icon="fas fa-lock" color="success" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.open_sessions') }}" value="{{ $data['stats']['open_sessions'] }}" icon="fas fa-lock-open" color="warning" />
                </div>
                <div class="col-md-3 col-sm-6">
                    <x-dashboard.stat-card class="text-center h-100 bg-sms-primary text-white" title="{{ __('attendance_reports.avg_attendance') }}" value="{{ $data['stats']['attendance_percentage'] }}%" icon="fas fa-percent" color="light" />
                </div>
            </div>

            {{-- Sessions Log --}}
            <h5 class="fw-bold mb-3 mt-4">Session Log (Recent First)</h5>
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>{{ __('attendance_reports.date') }}</th>
                    <th>{{ __('attendance_reports.period') }}</th>
                    <th>{{ __('attendance_reports.subject') }}</th>
                    <th>{{ __('attendance_reports.section') }}</th>
                    <th class="text-center">{{ __('attendance_reports.status') }}</th>
                </x-slot:header>
                <x-slot:body>
                    @forelse($data['stats']['sessions'] as $session)
                        <tr>
                            <td>{{ $session->date?->format('D, d M Y') ?? '—' }}</td>
                            <td>{{ __('attendance_reports.period') }} {{ $session->period_number }}</td>
                            <td>{{ $session->subject->name ?? '—' }}</td>
                            <td>{{ $session->section->schoolClass->name ?? '' }} — {{ $session->section->name ?? '' }}</td>
                            <td class="text-center">
                                <x-shared.badge type="{{ $session->status->color() }}">
                                    <i class="fas fa-{{ $session->status === App\Enums\AttendanceSessionStatus::Locked ? 'lock' : 'lock-open' }} me-1"></i>
                                    {{ $session->status->label() }}
                                </x-shared.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-sms-muted">{{ __('attendance_reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>

        </x-shared.card>
    @else
        <x-shared.card class="border-0 shadow-sm text-center py-5">
            <i class="fas fa-search fa-3x mb-3 opacity-25 text-sms-muted"></i>
            <h5 class="text-sms-muted">Select a teacher to generate their report</h5>
        </x-shared.card>
    @endif
</div>
@endsection
