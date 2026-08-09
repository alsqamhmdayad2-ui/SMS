@extends('layouts.app')
@section('title', __('attendance_reports.student_report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance_reports.student_report') }}</h2>
            <p class="text-sms-muted mb-0">Detailed attendance history and per-subject breakdown for a specific student.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <x-shared.card class="mb-4 bg-sms-light" shadow="sm">
        <form action="{{ route('admin.attendance-reports.student') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-form.select name="student_id" label="{{ __('attendance_reports.student') }}" required>
                    <option value="">-- {{ __('attendance_reports.student') }} --</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}" {{ request('student_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }} ({{ $st->student_id }})
                        </option>
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
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="view" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i> عرض</button>
                    <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm" title="PDF"><i class="fas fa-file-pdf"></i></button>
                    <button type="submit" name="action" value="excel" class="btn btn-success btn-sm" title="Excel"><i class="fas fa-file-excel"></i></button>
                </div>
            </div>
        </form>
    </x-shared.card>

    @if($data)
        {{-- Report Content --}}
        <x-shared.card class="mb-4" shadow="sm">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                @if($data['student']->photo)
                    <img src="{{ asset('storage/'.$data['student']->photo) }}" class="rounded-circle shadow-sm" width="80" height="80" style="object-fit:cover;">
                @else
                    <div class="rounded-circle bg-sms-secondary text-white shadow-sm d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                        {{ strtoupper(substr($data['student']->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h3 class="mb-1 fw-bold">{{ $data['student']->name }}</h3>
                    <p class="text-sms-muted mb-0">
                        Student ID: <strong>{{ $data['student']->student_id }}</strong> &nbsp;|&nbsp;
                        {{ __('attendance_reports.section') }}: <strong>{{ $data['student']->section->grade->name ?? '' }} — {{ $data['student']->section->name ?? '' }}</strong>
                    </p>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-sms-muted small">Generated At</div>
                    <div class="fw-semibold">{{ $data['generated_at'] }}</div>
                </div>
            </div>

            {{-- Overall Stats --}}
            <h5 class="fw-bold mb-3">Overall Statistics</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100 bg-sms-primary text-white" title="{{ __('attendance_reports.attendance_rate') }}" value="{{ $data['stats']['attendance_percentage'] }}%" icon="fas fa-percent" color="light" />
                </div>
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.total_sessions') }}" value="{{ $data['stats']['total_sessions'] }}" icon="fas fa-calendar-alt" color="primary" />
                </div>
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.present') }}" value="{{ $data['stats']['present_count'] }}" icon="fas fa-check-circle" color="success" />
                </div>
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.absent') }}" value="{{ $data['stats']['absent_count'] }}" icon="fas fa-times-circle" color="danger" />
                </div>
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.late') }}" value="{{ $data['stats']['late_count'] }}" icon="fas fa-clock" color="warning" />
                </div>
                <div class="col-md-2 col-sm-4">
                    <x-dashboard.stat-card class="text-center h-100" title="{{ __('attendance_reports.excused') }}" value="{{ $data['stats']['excused_count'] + $data['stats']['sick_count'] }}" icon="fas fa-file-alt" color="info" />
                </div>
            </div>

            {{-- Subject Breakdown --}}
            <h5 class="fw-bold mb-3 mt-5">Subject Breakdown</h5>
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>{{ __('attendance_reports.subject') }}</th>
                    <th class="text-center">{{ __('attendance_reports.total_sessions') }}</th>
                    <th class="text-center">{{ __('attendance_reports.present') }}</th>
                    <th class="text-center">{{ __('attendance_reports.absent') }}</th>
                    <th class="text-center">{{ __('attendance_reports.late') }}</th>
                    <th class="text-center">{{ __('attendance_reports.attendance_rate') }}</th>
                </x-slot:header>
                <x-slot:body>
                    @forelse($data['subject_breakdown'] as $sb)
                        <tr>
                            <td class="fw-semibold">{{ $sb['subject']->name ?? 'Unknown' }}</td>
                            <td class="text-center">{{ $sb['total'] }}</td>
                            <td class="text-center text-sms-success">{{ $sb['present'] }}</td>
                            <td class="text-center text-sms-danger">{{ $sb['absent'] }}</td>
                            <td class="text-center text-warning">{{ $sb['late'] }}</td>
                            <td class="text-center" style="width: 200px;">
                                <x-dashboard.progress value="{{ $sb['rate'] }}" showLabel="true" color="{{ $sb['rate'] >= 75 ? 'success' : ($sb['rate'] >= 50 ? 'warning' : 'danger') }}" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-sms-muted">{{ __('attendance_reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>

        </x-shared.card>
    @else
        <x-shared.card class="border-0 shadow-sm text-center py-5">
            <i class="fas fa-search fa-3x mb-3 opacity-25 text-sms-muted"></i>
            <h5 class="text-sms-muted">Select a student to generate their report</h5>
        </x-shared.card>
    @endif
</div>
@endsection
