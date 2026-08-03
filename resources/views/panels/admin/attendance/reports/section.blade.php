@extends('layouts.app')
@section('title', __('attendance_reports.section_report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance_reports.section_report') }}</h2>
            <p class="text-sms-muted mb-0">Overview of all students within a section ranked by attendance rate.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <x-shared.card class="mb-4 bg-sms-light" shadow="sm">
        <form action="{{ route('admin.attendance-reports.section') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-form.select name="section_id" label="{{ __('attendance_reports.section') }}" required>
                    <option value="">-- {{ __('attendance_reports.section') }} --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                            {{ $sec->grade->name ?? '' }} — {{ $sec->name }}
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
                <div class="rounded-circle bg-sms-primary bg-opacity-10 text-sms-primary d-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.5rem;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold">{{ $data['section']->grade->name ?? '' }} — {{ $data['section']->name ?? '' }}</h3>
                    <p class="text-sms-muted mb-0">{{ __('attendance_reports.student_count') }}: <strong>{{ count($data['students']) }}</strong></p>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-sms-muted small">Generated At</div>
                    <div class="fw-semibold">{{ $data['generated_at'] }}</div>
                </div>
            </div>

            {{-- Students Table --}}
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>#</th>
                    <th>{{ __('attendance_reports.student') }}</th>
                    <th class="text-center">{{ __('attendance_reports.total_sessions') }}</th>
                    <th class="text-center">{{ __('attendance_reports.present') }}</th>
                    <th class="text-center">{{ __('attendance_reports.absent') }}</th>
                    <th class="text-center">{{ __('attendance_reports.late') }}</th>
                    <th class="text-center">{{ __('attendance_reports.excused') }}</th>
                    <th class="text-center">{{ __('attendance_reports.attendance_rate') }}</th>
                    <th class="text-center">{{ __('attendance_reports.status') }}</th>
                </x-slot:header>
                <x-slot:body>
                    @forelse($data['students'] as $idx => $stData)
                        @php
                            $rate = $stData['attendance_percentage'];
                            if ($rate >= 90) {
                                $statusLabel = __('attendance_reports.excellent');
                                $statusColor = 'success';
                            } elseif ($rate >= 75) {
                                $statusLabel = __('attendance_reports.good');
                                $statusColor = 'primary';
                            } elseif ($rate >= 50) {
                                $statusLabel = __('attendance_reports.needs_attention');
                                $statusColor = 'warning';
                            } else {
                                $statusLabel = __('attendance_reports.critical');
                                $statusColor = 'danger';
                            }
                        @endphp
                        <tr>
                            <td class="text-sms-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $stData['student']->name }}</div>
                                <div class="text-sms-muted small">{{ $stData['student']->student_id }}</div>
                            </td>
                            <td class="text-center">{{ $stData['total_sessions'] }}</td>
                            <td class="text-center text-sms-success">{{ $stData['present_count'] }}</td>
                            <td class="text-center text-sms-danger fw-bold">{{ $stData['absent_count'] > 0 ? $stData['absent_count'] : '-' }}</td>
                            <td class="text-center text-warning">{{ $stData['late_count'] > 0 ? $stData['late_count'] : '-' }}</td>
                            <td class="text-center text-info">{{ ($stData['excused_count'] + $stData['sick_count']) > 0 ? ($stData['excused_count'] + $stData['sick_count']) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-sms-{{ $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }} px-2 py-1 fs-6">
                                    {{ $rate }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <x-shared.badge type="{{ $statusColor }}">
                                    {{ $statusLabel }}
                                </x-shared.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-sms-muted">{{ __('attendance_reports.no_data') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>

        </x-shared.card>
    @else
        <x-shared.card class="border-0 shadow-sm text-center py-5">
            <i class="fas fa-search fa-3x mb-3 opacity-25 text-sms-muted"></i>
            <h5 class="text-sms-muted">Select a section to generate the report</h5>
        </x-shared.card>
    @endif
</div>
@endsection
