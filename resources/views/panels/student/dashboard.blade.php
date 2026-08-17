@extends('layouts.app')
@section('title', 'لوحة تحكم الطالب')

@section('content')

<x-page-header
    title="لوحة تحكم الطالب">
    <x-slot:actions>
        <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l، d F Y') }}</span>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'لوحة التحكم']
]" />

<!-- Welcome Card -->
<div class="card mb-4 card-welcome">
    <div class="card-body">
        <div class="card-welcome-content">
            <div class="card-welcome-text w-100">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2 class="m-0">أهلاً {{ $student->name ?? 'طالب' }}! 👋</h2>
                </div>

                <div class="mt-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2);">
                    <h5 class="mb-3 text-white">
                        <i class="fas fa-id-card me-2"></i>معلوماتي
                    </h5>
                    <div class="row g-3 text-white">
                        <div class="col-sm-6 col-lg-3">
                            <i class="fas fa-hashtag me-1"></i>
                            <strong>رقم الطالب:</strong>
                            <span style="direction: ltr; display: inline-block; font-weight: bold; color: #ffeb3b;">{{ $student->id ?? '-' }}</span>
                            ⭐
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <i class="fas fa-layer-group me-1"></i>
                            <strong>المرحلة:</strong> {{ $student->grade->name ?? '-' }}
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <i class="fas fa-door-open me-1"></i>
                            <strong>الصف:</strong> {{ trim(str_replace('الصف', '', $student->schoolClass->name ?? '-')) }} ({{ str_replace(['الشعبة ', 'شعبة '], '', $student->section->name ?? '-') }})
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <strong>السنة الدراسية:</strong> {{ $academicYear->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
    <div class="col">
        <div class="stat-card slide-up h-100 blue">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-details">
                <h3>{{ $attendanceStats['attendance_percentage'] ?? 0 }}%</h3>
                <p>نسبة الحضور</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card slide-up h-100 green" style="animation-delay: 0.1s;">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-details">
                <h3>{{ $resultsSummary['overall_gpa'] ?? '-' }}</h3>
                <p>المعدل التراكمي (GPA)</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card slide-up h-100 purple" style="animation-delay: 0.2s;">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-details">
                <h3>{{ $resultsSummary['total_subjects'] ?? 0 }}</h3>
                <p>المواد الدراسية</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card slide-up h-100 orange" style="animation-delay: 0.3s;">
            <div class="stat-icon"><i class="fas fa-bell"></i></div>
            <div class="stat-details">
                <h3>{{ $upcomingExams->count() }}</h3>
                <p>اختبارات قادمة</p>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Widget -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white border-light pt-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="fw-bold m-0 fs-5">
                    <i class="fas fa-calendar-alt text-primary" style="margin-inline-start: 8px"></i>
                    جدول الحصص
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

<div class="row g-4">
    <!-- Available Exams -->
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h3>
                    <i class="fas fa-file-alt text-info" style="margin-inline-start: 8px"></i>
                    الاختبارات المتاحة
                </h3>
                <a href="{{ route('student.exams') }}" class="btn btn-outline btn-sm">عرض الكل</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover border-top data-table" style="min-width: 0">
                        <thead>
                            <tr>
                                <th>الاختبار</th>
                                <th>المادة</th>
                                <th>المعلم</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingExams as $exam)
                                <tr>
                                    <td>{{ $exam->title }}</td>
                                    <td>{{ $exam->subject->name ?? '-' }}</td>
                                    <td>{{ $exam->teacher ? $exam->teacher->user->name : '-' }}</td>
                                    <td>
                                        <a href="{{ route('student.exams') }}" class="btn btn-sm btn-secondary" style="padding: 4px 10px">عرض التفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-times fs-4 mb-2 d-block"></i>
                                            لا توجد اختبارات قادمة
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects & Grades -->
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h3>
                    <i class="fas fa-chart-line text-accent" style="margin-inline-start: 8px"></i>
                    درجاتي
                </h3>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    @forelse($subjectResults as $result)
                        <li class="info-item">
                            <span class="info-label">{{ $result['subject']->name }}</span>
                            <span class="info-value">
                                @if($result['is_published'])
                                    <span class="badge {{ ($result['is_passing'] ?? false) ? 'badge-success' : 'badge-warning' }}">
                                        {{ $result['total_percentage'] }} / 100
                                    </span>
                                @else
                                    <span class="badge badge-secondary">قريباً</span>
                                @endif
                            </span>
                        </li>
                    @empty
                        <li class="info-item border-0">
                            <div class="text-center text-muted w-100 py-3">
                                <i class="fas fa-chart-line fs-4 mb-2 d-block opacity-50"></i>
                                لا توجد درجات حتى الآن
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

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
