@extends('layouts.app')
@section('title', 'نتائج الطالب: '.$student->name)

@section('content')

<x-page-header title="نتائج الطالب: {{ $student->name }}">
    <x-slot name="actions">
        <a href="{{ route('admin.students.result.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
        @if($result ?? false)
        <a href="{{ route('admin.students.result.print', ['student' => $student->id, 'academic_year_id' => $selectedYear, 'semester_id' => $selectedSemester]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> طباعة / PDF
        </a>
        @endif
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'نتائج الطلاب', 'url' => route('admin.students.result.index')],
    ['name' => 'نتائج الطالب']
]" />

<div class="">

    <!-- Filters -->
    <x-shared.card class="mb-4 bg-light" shadow="sm">
        <form action="{{ route('admin.students.result.show', $student->id) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <x-form.select name="academic_year_id" label="Academic Year" required="true">
                    @foreach($academicYears as $y)
                    <option value="{{ $y->id }}" {{ $selectedYear == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="semester_id" label="Semester">
                    <option value="">Full Year</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $selectedSemester == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat"></i> Load</button>
            </div>
        </form>
    </x-shared.card>

    @if($result && count($result['subjects']) > 0)
    <!-- Header Card -->
    <x-shared.card class="border-start border-sms-primary border-5 mb-4" shadow="sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold mb-1">{{ $student->name }}</h4>
                <p class="text-sms-muted mb-0">
                    <strong>Grade:</strong> {{ $student->grade->name ?? 'N/A' }} &mdash; 
                    <strong>Class:</strong> {{ $student->schoolClass->name ?? 'N/A' }} &mdash; 
                    <strong>Section:</strong> {{ $student->section->name ?? 'N/A' }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex justify-content-end gap-3 text-center">
                    <div>
                        <div class="text-sms-muted small text-uppercase">Overall GPA</div>
                        <div class="fs-3 fw-bold text-sms-primary">{{ $result['summary']['overall_gpa'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sms-muted small text-uppercase">Average</div>
                        <div class="fs-3 fw-bold text-sms-success">{{ $result['summary']['average_percentage'] }}%</div>
                    </div>
                    <div>
                        <div class="text-sms-muted small text-uppercase">Status</div>
                        @if($result['summary']['status'] == 'passed')
                            <div class="fs-3 fw-bold text-sms-success"><i class="bi bi-check-circle-fill"></i> Pass</div>
                        @elseif($result['summary']['status'] == 'failed')
                            <div class="fs-3 fw-bold text-sms-danger"><i class="bi bi-x-circle-fill"></i> Fail</div>
                        @else
                            <div class="fs-3 fw-bold text-warning"><i class="bi bi-exclamation-circle-fill"></i> Inc</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-shared.card>

    <!-- Subjects Table -->
    <x-shared.card shadow="sm" class="mb-4">
        <x-slot:header>
            <h6 class="m-0 fw-bold"><i class="bi bi-list-task"></i> Subject Results</h6>
        </x-slot:header>
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>Subject</th>
                <th>Components</th>
                <th class="text-center">Total Score</th>
                <th class="text-center">Grade</th>
                <th class="text-center">GPA</th>
                <th class="text-center">Class Rank</th>
                <th class="text-center">Status</th>
            </x-slot:header>
            <x-slot:body>
                @foreach($result['subjects'] as $index => $subResult)
                @php
                    $rowColor = '';
                    if($subResult['total_percentage'] >= 90) $rowColor = 'bg-sms-success bg-opacity-10';
                    elseif($subResult['is_passing'] === false) $rowColor = 'bg-sms-danger bg-opacity-10';
                @endphp
                <tr class="{{ $rowColor }}">
                    <td class="fw-bold">
                        {{ $subResult['subject']->name }}
                        @if($subResult['is_finalized'])
                        <br><x-shared.badge type="secondary" class="mt-1" style="font-size:0.6rem"><i class="bi bi-lock-fill"></i> Finalized</x-shared.badge>
                        @endif
                        @if(!$subResult['is_published'])
                        <br><x-shared.badge type="warning" class="text-dark mt-1" style="font-size:0.6rem"><i class="bi bi-eye-slash-fill"></i> Unpublished</x-shared.badge>
                        @endif
                    </td>
                    <td>
                        <!-- Quick breakdown -->
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($subResult['components'] as $comp)
                            <div class="border rounded px-2 py-1 bg-white" style="font-size: 0.8rem;">
                                <span class="text-sms-muted">{{ $comp['code'] }}:</span> 
                                <strong>{{ $comp['obtained'] }}/{{ $comp['total'] }}</strong>
                                @if($comp['contribution'] !== null)
                                    <span class="text-sms-primary">({{ $comp['contribution'] }}%)</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="text-center fs-5 fw-bold">{{ $subResult['total_percentage'] }}%</td>
                    <td class="text-center">
                        @if($subResult['letter_grade'])
                        <x-shared.badge :type="$subResult['is_passing'] ? 'success' : 'danger'" class="fs-6">{{ $subResult['letter_grade'] }}</x-shared.badge>
                        @else - @endif
                    </td>
                    <td class="text-center fw-bold">{{ $subResult['gpa_points'] ?? '-' }}</td>
                    <td class="text-center fw-bold text-sms-muted">{{ $subResult['rank'] ? $subResult['rank'] : '-' }}</td>
                    <td class="text-center">
                        @if($subResult['is_passing'] === true)
                        <x-shared.badge type="success">Pass</x-shared.badge>
                        @elseif($subResult['is_passing'] === false)
                        <x-shared.badge type="danger">Fail</x-shared.badge>
                        @else - @endif
                    </td>
                </tr>
                @endforeach
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>

    <!-- Summary Footers -->
    <div class="row text-center mb-4 g-3">
        <div class="col-md-3">
            <x-dashboard.stat-card class="text-center" title="Total Subjects" value="{{ $result['summary']['total_subjects'] }}" />
        </div>
        <div class="col-md-3">
            <x-dashboard.stat-card class="text-center" title="Passed" value="{{ $result['summary']['passed'] }}" color="success" />
        </div>
        <div class="col-md-3">
            <x-dashboard.stat-card class="text-center" title="Failed" value="{{ $result['summary']['failed'] }}" color="danger" />
        </div>
        <div class="col-md-3">
            <x-dashboard.stat-card class="text-center" title="Class Rank" value="-" color="primary" />
        </div>
    </div>

    @elseif(request()->has('academic_year_id'))
    <div class="text-center py-5">
        <x-shared.empty-state icon="inbox" title="No results found" message="No results found for this student in the selected period." />
    </div>
    @else
    <div class="text-center py-5">
        <x-shared.empty-state icon="funnel" title="Select Academic Year" message="Select Academic Year to load results." />
    </div>
    @endif
</div>
@endsection
