@extends('layouts.app')
@section('title', 'التقرير السنوي')

@section('content')
<x-page-header title="التقرير السنوي الشامل" />

@if(($type ?? '') === 'student')
{{-- ========== STUDENT ANNUAL REPORT ========== --}}
<div class="card border-0 shadow mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ $student->name }}</h4>
                <div class="text-muted small">
                    {{ $student->section->schoolClass->name ?? '' }} &mdash; {{ $student->section->name ?? '' }}
                    &nbsp;|&nbsp; الرقم الأكاديمي: {{ $student->student_id ?? $student->id }}
                </div>
            </div>
            <div class="text-end">
                @if($annual_avg !== null)
                <div class="fs-4 fw-bold {{ ($annual_pass ?? false) ? 'text-success' : 'text-danger' }}">
                    {{ $annual_avg }}%
                </div>
                <span class="badge {{ ($annual_pass ?? false) ? 'bg-success' : 'bg-danger' }} fs-6">
                    {{ $annual_grade ?? '-' }}
                </span>
                @endif
                <div class="text-muted small mt-1">{{ $generated_at ?? '' }}</div>
            </div>
        </div>
    </div>
</div>

@foreach($semesters as $semData)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">{{ $semData['semester']->name }}</h6>
        @if($semData['average'] !== null)
        <span>
            <span class="fw-bold">{{ $semData['average'] }}%</span>
            &nbsp;
            <span class="badge {{ ($semData['is_passing'] ?? false) ? 'bg-success' : 'bg-danger' }}">{{ $semData['letter_grade'] ?? '-' }}</span>
        </span>
        @else
        <span class="text-muted small">لا توجد درجات</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المادة</th>
                        <th class="text-center" style="width:100px">النسبة %</th>
                        <th class="text-center" style="width:100px">التقدير</th>
                        <th class="text-center" style="width:80px">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semData['subjects'] as $row)
                    <tr>
                        <td>{{ $row['subject']->name }}</td>
                        <td class="text-center fw-bold">
                            {{ $row['percentage'] !== null ? $row['percentage'].'%' : '-' }}
                        </td>
                        <td class="text-center">
                            @if($row['letter_grade'] !== '-')
                            <span class="badge {{ ($row['is_passing'] ?? null) === true ? 'bg-success' : (($row['is_passing'] === false) ? 'bg-danger' : 'bg-secondary') }}">
                                {{ $row['letter_grade'] }}
                            </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($row['is_passing'] ?? null) === true)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @elseif(($row['is_passing'] ?? null) === false)
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            @else
                                <i class="bi bi-dash-circle text-muted"></i>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@if($annual_avg !== null)
<div class="alert {{ ($annual_pass ?? false) ? 'alert-success' : 'alert-danger' }} d-flex justify-content-between align-items-center">
    <span class="fw-bold fs-5">النتيجة السنوية النهائية</span>
    <span>
        <strong>{{ $annual_avg }}%</strong> &mdash;
        <span class="badge {{ ($annual_pass ?? false) ? 'bg-success' : 'bg-danger' }} fs-6">{{ $annual_grade ?? '-' }}</span>
    </span>
</div>
@endif

@else
{{-- ========== SECTION ANNUAL REPORT ========== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width:40px">#</th>
                        <th>اسم الطالب</th>
                        @foreach($semesters as $sem)
                        <th class="text-center">متوسط {{ $sem->name }}</th>
                        @endforeach
                        <th class="text-center">المتوسط السنوي</th>
                        <th class="text-center">التقدير</th>
                        <th class="text-center">النتيجة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $row)
                    <tr class="{{ ($row['is_passing'] ?? null) === false ? 'table-danger' : '' }}">
                        <td class="text-center text-muted">{{ $row['rank'] ?? loop.index }}</td>
                        <td><strong>{{ $row['student']->name }}</strong></td>
                        @foreach($semesters as $sem)
                        <td class="text-center">
                            {{ isset($row['semester_averages'][$sem->id]) ? $row['semester_averages'][$sem->id].'%' : '-' }}
                        </td>
                        @endforeach
                        <td class="text-center fw-bold">
                            {{ $row['annual_avg'] !== null ? $row['annual_avg'].'%' : '-' }}
                        </td>
                        <td class="text-center">
                            @if(($row['letter_grade'] ?? '-') !== '-')
                            <span class="badge {{ ($row['is_passing'] ?? null) === true ? 'bg-success' : 'bg-danger' }}">
                                {{ $row['letter_grade'] }}
                            </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($row['is_passing'] ?? null) === true)
                                <span class="badge bg-success">ناجح</span>
                            @elseif(($row['is_passing'] ?? null) === false)
                                <span class="badge bg-danger">راسب</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="99" class="text-center py-4 text-muted">لا توجد بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
