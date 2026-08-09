@extends('layouts.app')
@section('title', __('attendance_reports.monthly_report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ __('attendance_reports.monthly_report') }}</h2>
            <p class="text-sms-muted mb-0">Overview of attendance trends and rankings for a specific month.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <x-shared.card class="mb-4 bg-sms-light" shadow="sm">
        <form action="{{ route('admin.attendance-reports.monthly') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <x-form.input type="month" name="month" value="{{ $month }}" label="{{ __('attendance_reports.select_month') }}" required />
            </div>
            <div class="col-md-3">
                <x-form.select name="section_id" label="{{ __('attendance_reports.section') }}">
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
                <div class="rounded-circle bg-sms-primary bg-opacity-10 text-sms-primary d-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.5rem;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h3>
                    <p class="text-sms-muted mb-0">{{ __('attendance_reports.total_days') }}: <strong>{{ count($data['daily_data']) }}</strong></p>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-sms-muted small">Generated At</div>
                    <div class="fw-semibold">{{ $data['generated_at'] }}</div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Daily Breakdown Table (Mock Trend) --}}
                <div class="col-md-7">
                    <h5 class="fw-bold mb-3">{{ __('attendance_reports.daily_trend') }}</h5>
                    <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                        <x-table.data-table hover="true">
                            <x-slot:header>
                                <th>{{ __('attendance_reports.date') }}</th>
                                <th>{{ __('attendance_reports.present') }}</th>
                                <th>{{ __('attendance_reports.absent') }}</th>
                                <th>{{ __('attendance_reports.late') }}</th>
                                <th>{{ __('attendance_reports.attendance_rate') }}</th>
                            </x-slot:header>
                            <x-slot:body>
                                @forelse($data['daily_data'] as $day)
                                    @php
                                        $rate = $day['total'] > 0 ? round(($day['present'] / $day['total']) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-start fw-semibold">{{ \Carbon\Carbon::parse($day['date'])->format('D, M j') }}</td>
                                        <td class="text-sms-success">{{ $day['present'] }}</td>
                                        <td class="text-sms-danger">{{ $day['absent'] }}</td>
                                        <td class="text-warning">{{ $day['late'] }}</td>
                                        <td>
                                            <span class="badge bg-sms-{{ $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }} w-100">
                                                {{ $rate }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-sms-muted py-4 text-center">{{ __('attendance_reports.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </x-slot:body>
                        </x-table.data-table>
                    </div>
                </div>

                {{-- Absentees & Rankings --}}
                <div class="col-md-5">
                    <h5 class="fw-bold mb-3">{{ __('attendance_reports.lowest_sections') }}</h5>
                    <ul class="list-group list-group-flush mb-4 border rounded">
                        @forelse(array_slice($data['rankings'], 0, 5) as $rank)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 border-bottom border-light">
                                <span>{{ $rank['section']->grade->name ?? '' }} — {{ $rank['section']->name ?? '' }}</span>
                                <span class="badge bg-sms-{{ $rank['rate'] >= 75 ? 'success' : ($rank['rate'] >= 50 ? 'warning' : 'danger') }} rounded-pill">
                                    {{ $rank['rate'] }}%
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item px-3 text-sms-muted">{{ __('attendance_reports.no_ranking') }}</li>
                        @endforelse
                    </ul>

                    <h5 class="fw-bold mb-3">{{ __('attendance_reports.top_absentees') }}</h5>
                    <ul class="list-group list-group-flush border rounded">
                        @forelse(array_slice($data['absentees'], 0, 5) as $abs)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 border-bottom border-light">
                                <div class="d-flex align-items-center gap-2">
                                    @if($abs['student']->photo)
                                        <img src="{{ asset('storage/'.$abs['student']->photo) }}" class="rounded-circle" width="30" height="30" style="object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-sms-secondary text-white d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:12px;">
                                            {{ strtoupper(substr($abs['student']->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="small fw-semibold">{{ $abs['student']->name }}</span>
                                </div>
                                <span class="text-sms-danger fw-bold">{{ $abs['absent_count'] }} <small class="text-sms-muted fw-normal">{{ __('attendance_reports.abs') }}</small></span>
                            </li>
                        @empty
                            <li class="list-group-item px-3 text-sms-muted">{{ __('attendance_reports.no_absentees') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </x-shared.card>
    @endif
</div>
@endsection
