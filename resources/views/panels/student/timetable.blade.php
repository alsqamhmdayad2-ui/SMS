@extends('layouts.app')
@section('title', 'الجدول الدراسي')

@section('content')

<x-page-header title="الجدول الدراسي">
    <x-slot:actions>
        <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l، d F Y') }}</span>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الجدول الدراسي']
]" />

@php
    $daysArabic = [
        'Sunday' => 'الأحد',
        'Monday' => 'الإثنين',
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس',
    ];
    $weeklySchedule = $timetable->groupBy('day_of_week');
    // Find the maximum period number to render enough columns, default to 8
    $maxPeriods = $timetable->max('period_number') ?? 8;
    if ($maxPeriods < 8) $maxPeriods = 8;
@endphp

<div class="card mb-4">
    <div class="card-body p-0">
        @if($timetable->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted">الجدول الدراسي غير متوفر حالياً</h5>
                <p class="text-muted mb-0">يرجى مراجعة الإدارة المدرسية لتسكين الحصص.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle mb-0" style="min-width: 900px;">
                    <thead class="table-light">
                        <tr>
                            <th class="bg-primary text-white border-primary" style="width: 12%;">اليوم / الحصة</th>
                            @for($i = 1; $i <= $maxPeriods; $i++)
                                <th class="py-3">
                                    الحصة {{ $i }}
                                    @php
                                        // Try to find the time for this period from any day to display in the header
                                        $samplePeriod = $timetable->firstWhere('period_number', $i);
                                    @endphp
                                    @if($samplePeriod && $samplePeriod->start_time && $samplePeriod->end_time)
                                        <br>
                                        <small class="fw-normal text-muted" dir="ltr">
                                            {{ \Carbon\Carbon::parse($samplePeriod->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($samplePeriod->end_time)->format('H:i') }}
                                        </small>
                                    @endif
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daysArabic as $enDay => $arDay)
                            @php
                                $dayPeriods = $weeklySchedule->get($enDay, $weeklySchedule->get($arDay, collect()));
                            @endphp
                            
                            @if($dayPeriods->isNotEmpty())
                                <tr>
                                    <th class="table-light text-center align-middle fs-6">{{ $arDay }}</th>
                                    @for($i = 1; $i <= $maxPeriods; $i++)
                                        @php
                                            $period = $dayPeriods->firstWhere('period_number', $i);
                                        @endphp
                                        @if($period)
                                            <td class="p-3">
                                                <div class="fw-bold text-primary mb-1">{{ $period->subject->name ?? '-' }}</div>
                                                <div class="small text-muted"><i class="fas fa-user-tie me-1"></i>{{ $period->teacher ? $period->teacher->user->name : '-' }}</div>
                                            </td>
                                        @else
                                            <td class="text-muted align-middle bg-light bg-opacity-50">-</td>
                                        @endif
                                    @endfor
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
