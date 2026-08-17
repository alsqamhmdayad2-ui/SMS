@extends('layouts.app')
@section('title', 'جدول الحصص - ولي الأمر')

@section('content')

<x-page-header title="جدول الحصص">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'جدول الحصص']
]" />

<div class="card mb-4 shadow-sm border-0 slide-up">
    <div class="card-body">
        <form action="{{ route('parent.timetable') }}" method="GET" class="row align-items-end" id="childSelectForm">
            <div class="col-md-6">
                <label for="student_id" class="form-label fw-bold"><i class="fas fa-child text-primary me-2"></i>اختر الابن</label>
                <select name="student_id" id="student_id" class="form-select form-select-lg" onchange="document.getElementById('childSelectForm').submit()">
                    @forelse($children as $child)
                        <option value="{{ $child->id }}" {{ $selectedChild && $selectedChild->id == $child->id ? 'selected' : '' }}>
                            {{ $child->name }}
                        </option>
                    @empty
                        <option value="">لا يوجد أبناء مضافين</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>
</div>

@if($selectedChild)
<!-- Schedule Widget -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card h-100 shadow-sm border-0 slide-up" style="animation-delay: 0.1s;">
            <div class="card-header bg-white border-light pt-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="fw-bold m-0 fs-5">
                    <i class="fas fa-calendar-alt text-primary" style="margin-inline-start: 8px"></i>
                    جدول الحصص: {{ $selectedChild->name }}
                </h3>
                <div class="btn-group" role="group" aria-label="Toggle Schedule">
                    <button type="button" class="btn btn-primary px-4" id="btnDaily" onclick="showDaily()">اليومي</button>
                    <button type="button" class="btn btn-outline-primary px-4" id="btnWeekly" onclick="showWeekly()">الأسبوعي</button>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Daily Schedule -->
                <div id="dailySchedule" class="fade-in">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center data-table d-table w-100 border-top">
                            <thead class="table-light">
                                <tr class="text-center align-middle">
                                    <th>الحصة</th>
                                    <th>المادة</th>
                                    <th>المعلم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $periodNames = [
                                        1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة',
                                        5 => 'الخامسة', 6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة',
                                    ];
                                    $periodColors = [
                                        1 => 'text-primary', 2 => 'text-success', 3 => 'text-info',
                                        4 => 'text-warning', 5 => 'text-danger', 6 => 'text-secondary',
                                        7 => 'text-primary', 8 => 'text-success',
                                    ];
                                @endphp
                                @forelse($dailySchedule as $period)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $periodNames[$period->period_number] ?? 'حصة '.$period->period_number }}</span></td>
                                        <td class="fw-bold {{ $periodColors[$period->period_number] ?? 'text-dark' }}">{{ $period->subject->name ?? '-' }}</td>
                                        <td>{{ $period->teacher ? $period->teacher->user->name : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-bed fs-4 mb-2 d-block"></i>
                                                لا توجد حصص في هذا اليوم
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Weekly Schedule -->
                <div id="weeklySchedule" class="d-none fade-in">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 text-center" style="min-width: 700px">
                            <thead class="table-light">
                                <tr>
                                    <th class="bg-primary text-white border-primary" style="width: 15%">اليوم / الحصة</th>
                                    <th class="py-3">1</th>
                                    <th class="py-3">2</th>
                                    <th class="py-3">3</th>
                                    <th class="py-3">4</th>
                                    <th class="py-3">5</th>
                                    <th class="py-3">6</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daysArabic as $enDay => $arDay)
                                    @php
                                        $dayPeriods = $weeklySchedule->get($enDay, $weeklySchedule->get($arDay, collect()));
                                    @endphp
                                    @if($dayPeriods->isNotEmpty())
                                        <tr>
                                            <th class="table-light text-center align-middle">{{ $arDay }}</th>
                                            @for($i = 1; $i <= 6; $i++)
                                                @php
                                                    $maxPeriods = 6;
                                                    $period = $dayPeriods->firstWhere('period_number', $i);
                                                    $color = $periodColors[$i] ?? 'text-dark';
                                                @endphp
                                                @if($period)
                                                    <td class="fw-bold {{ $color }}" title="{{ $period->teacher ? $period->teacher->user->name : '' }}">
                                                        {{ $period->subject->name ?? '-' }}
                                                    </td>
                                                @else
                                                    <td class="text-muted">-</td>
                                                @endif
                                            @endfor
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<x-empty-state message="لم يتم العثور على الابن المطلوب أو لا يوجد أبناء مسجلين" icon="fas fa-child" />
@endif

@endsection

@push('scripts')
<script>
    function showDaily() {
        document.getElementById('dailySchedule').classList.remove('d-none');
        document.getElementById('weeklySchedule').classList.add('d-none');
        document.getElementById('btnDaily').classList.replace('btn-outline-primary', 'btn-primary');
        document.getElementById('btnWeekly').classList.replace('btn-primary', 'btn-outline-primary');
    }
    function showWeekly() {
        document.getElementById('weeklySchedule').classList.remove('d-none');
        document.getElementById('dailySchedule').classList.add('d-none');
        document.getElementById('btnWeekly').classList.replace('btn-outline-primary', 'btn-primary');
        document.getElementById('btnDaily').classList.replace('btn-primary', 'btn-outline-primary');
    }
</script>
@endpush
