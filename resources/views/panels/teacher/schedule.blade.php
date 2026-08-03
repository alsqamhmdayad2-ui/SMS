@extends('layouts.app')
@section('title', 'جدول الحصص')

@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="page-title">
        <h2>
            <i class="fas fa-calendar-alt" style="margin-inline-start:10px;color:var(--secondary)"></i>
            جدول الحصص
        </h2>
        <ul class="breadcrumb mt-2 mb-0">
            <li><a href="{{ route('teacher.dashboard') }}">لوحة التحكم</a></li>
            <li>جدول الحصص</li>
        </ul>
    </div>
</div>

@if(collect($timetables)->isEmpty())
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-4x mb-3 d-block opacity-40"></i>
            <h5>لا يوجد جدول دراسي مسند إليك بعد</h5>
        </div>
    </div>
@else
    @php
        $maxPeriod = 0;
        foreach($timetables as $slots) {
            $max = $slots->max('period_number');
            if($max > $maxPeriod) $maxPeriod = $max;
        }
        $maxPeriod = max(6, $maxPeriod); 
        
        // Days are stored in Arabic in the DB
        $orderedDays = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
    @endphp

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle mb-0" style="min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">اليوم \ الحصة</th>
                            @for($i = 1; $i <= $maxPeriod; $i++)
                                <th>الحصة {{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderedDays as $dayAr)
                            <tr>
                                <th class="table-light">{{ $dayAr }}</th>
                                @for($i = 1; $i <= $maxPeriod; $i++)
                                    @php
                                        // The keys in $timetables are the database values (Arabic)
                                        $slot = isset($timetables[$dayAr]) ? $timetables[$dayAr]->firstWhere('period_number', $i) : null;
                                    @endphp
                                    <td style="width: calc(100% / {{ $maxPeriod + 1 }});">
                                        @if($slot)
                                            <div class="fw-bold text-dark">
                                                {{ $slot->section?->schoolClass?->name ?? '' }} ({{ trim(str_replace(['الشعبة', 'شعبة'], '', $slot->section?->name ?? '—')) }})
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.85rem;">
                                                {{ $slot->subject?->name ?? '—' }}
                                            </div>
                                        @else
                                            <span class="text-muted opacity-25">—</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- Quick Action --}}
<div class="mt-4 text-center">
    <a href="{{ route('teacher.attendance.today') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-clipboard-check me-2"></i>تحضير طلاب اليوم
    </a>
</div>

@endsection
