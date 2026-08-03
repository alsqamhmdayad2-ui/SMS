@extends('layouts.app')
@section('title', 'لوحة التحكم - معلم')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <h2>
        <i class="fas fa-chalkboard-teacher" style="margin-inline-start:10px; color:var(--secondary)"></i>
        مرحباً {{ $teacher?->first_name ?? auth()->user()->name }}
    </h2>
    <ul class="breadcrumb">
        <li>لوحة التحكم</li>
    </ul>
</div>

{{-- ─── Stat Cards ─────────────────────────────────────────────────── --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">

    {{-- Total Students --}}
    <div class="col">
        <div class="stat-card slide-up h-100 blue">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-content">
                <h3>{{ $totalStudents }}</h3>
                <p>عدد طلابي</p>
            </div>
        </div>
    </div>

    {{-- My Sections --}}
    <div class="col">
        <div class="stat-card slide-up h-100 purple">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-content">
                <h3>{{ $mySections->count() }}</h3>
                <p>شُعبي</p>
            </div>
        </div>
    </div>

    {{-- Today's Classes --}}
    <div class="col">
        <div class="stat-card slide-up h-100 green">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-content">
                <h3>{{ $todaySchedule->count() }}</h3>
                <p>حصص اليوم</p>
            </div>
        </div>
    </div>

    {{-- Absent Today --}}
    <div class="col">
        <div class="stat-card slide-up h-100 red">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                <h3>{{ $absentToday }}</h3>
                <p>طلاب غائبون اليوم</p>
            </div>
        </div>
    </div>

</div>

{{-- ─── Main Grid ──────────────────────────────────────────────────── --}}
<div class="row g-4">

    {{-- My Sections Table --}}
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">أقسامي</h3>
                <a href="{{ route('teacher.attendance.today') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-clipboard-check me-1"></i> حضور الطلاب
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table data-table border-top mb-0">
                        <thead>
                            <tr>
                                <th>المرحلة</th>
                                <th>الصف</th>
                                <th>الشعبة</th>
                                <th>الطلاب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mySections as $section)
                                <tr>
                                    <td>{{ $section->schoolClass?->grade?->name ?? '—' }}</td>
                                    <td>{{ $section->schoolClass?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $section->name }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $section->students->count() }}</span> طالب
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher.students', ['section_id' => $section->id]) }}"
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> عرض
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                        لا توجد شعب مسندة إليك
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Schedule --}}
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-calendar-alt me-2 text-sms-primary"></i>
                    جدول اليوم —
                    <small class="text-sms-muted fs-6">{{ $today->translatedFormat('l، j F') }}</small>
                </h3>
            </div>
            <div class="card-body p-0">
                @if($todaySchedule->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-40 d-block"></i>
                        <p class="mb-0">لا توجد حصص اليوم</p>
                    </div>
                @else
                    <ul class="info-list mb-0">
                        @foreach($todaySchedule as $tt)
                            <li class="info-item px-3 py-2">
                                <div class="d-flex align-items-center gap-3 w-100">
                                    <div class="schedule-time-box text-center"
                                         style="min-width:70px;background:var(--bg-body);padding:5px 8px;border-radius:8px;border:1px solid var(--border-color);">
                                        <small class="text-muted d-block" style="font-size:.65rem">الحصة</small>
                                        <span class="fw-bold" style="font-size:.85rem">{{ $tt->period_number }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="fw-semibold">
                                                {{ $tt->section?->name ?? '—' }}
                                            </span>
                                            <span class="badge badge-info" style="font-size:.72rem">
                                                {{ $tt->subject?->name ?? '—' }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            {{ $tt->section?->schoolClass?->grade?->name ?? '' }}
                                        </small>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                <a href="{{ route('teacher.attendance.today') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-clipboard-check me-1"></i> حضور الطلاب
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
