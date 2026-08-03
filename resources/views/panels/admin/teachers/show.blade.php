@extends('layouts.app')
@section('title', 'ملف المعلم')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-chalkboard-teacher me-2"></i> ملف المعلم</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-secondary">
            <i class="fas fa-edit me-1"></i> تعديل
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white mx-auto mb-3"
                     style="width:120px;height:120px;font-size:3rem;background:var(--gradient-primary);">
                    {{ mb_substr($teacher->first_name, 0, 1) }}
                </div>
                <h4 class="fw-bold mb-1">{{ $teacher->full_name }}</h4>
                <p class="text-muted mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                        {{ $teacher->specialization ?? 'تخصص عام' }}
                    </span>
                </p>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mt-1">
                    <i class="fas fa-check-circle me-1"></i> نشط
                </span>
            </div>
            <div class="card-footer bg-transparent">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-id-card me-2"></i> رقم الهوية</span>
                        <span class="fw-semibold" dir="ltr">{{ $teacher->national_id ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-envelope me-2"></i> البريد</span>
                        <span class="fw-semibold" dir="ltr" style="font-size:.85rem">{{ $teacher->user->email ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-phone me-2"></i> الهاتف</span>
                        <span class="fw-semibold" dir="ltr">{{ $teacher->phone ?? '—' }}</span>
                    </li>
                    @if($teacher->address)
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-map-marker-alt me-2"></i> العنوان</span>
                        <span class="fw-semibold" style="max-width:60%;text-align:end">{{ $teacher->address }}</span>
                    </li>
                    @endif
                    @if($teacher->salary)
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-money-bill-wave me-2"></i> الراتب</span>
                        <span class="fw-semibold text-success">{{ number_format($teacher->salary) }} ₪</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted small"><i class="fas fa-calendar-alt me-2"></i> تاريخ الانضمام</span>
                        <span class="fw-semibold">{{ $teacher->created_at?->format('Y/m/d') ?? '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Subjects and Schedule -->
    <div class="col-md-8">

        <!-- Subjects -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h3><i class="fas fa-book-open me-2"></i> المواد المسندة
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2">
                        {{ $teacher->subjects->count() ?? 0 }}
                    </span>
                </h3>
            </div>
            <div class="card-body">
                @if(isset($teacher->subjects) && $teacher->subjects->count())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($teacher->subjects as $subject)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2" style="font-size:.9rem">
                                <i class="fas fa-book me-1"></i> {{ $subject->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-book fa-2x mb-2 opacity-25 d-block"></i>
                        لا توجد مواد مسندة لهذا المعلم حالياً
                    </div>
                @endif
            </div>
        </div>

        <!-- Timetable Summary -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h3><i class="fas fa-calendar-week me-2"></i> الجدول الدراسي</h3>
            </div>
            <div class="card-body p-0">
                @php
                    $dayMap = [
                        'Saturday' => 'السبت', 'السبت' => 'السبت',
                        'Sunday' => 'الأحد', 'الأحد' => 'الأحد',
                        'Monday' => 'الإثنين', 'الإثنين' => 'الإثنين',
                        'Tuesday' => 'الثلاثاء', 'الثلاثاء' => 'الثلاثاء',
                        'Wednesday' => 'الأربعاء', 'الأربعاء' => 'الأربعاء',
                        'Thursday' => 'الخميس', 'الخميس' => 'الخميس'
                    ];
                    $standardDays = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
                    $maxPeriod = 7;
                    
                    $grid = [];
                    if (method_exists($teacher, 'timetables') && $teacher->timetables()->exists()) {
                        foreach($teacher->timetables()->with(['subject', 'section.schoolClass'])->get() as $t) {
                            $day = $dayMap[$t->day_of_week] ?? $t->day_of_week;
                            $grid[$day][$t->period_number] = $t;
                            // Find actual max period from data if it exceeds 7
                            if ($t->period_number > $maxPeriod) {
                                $maxPeriod = $t->period_number;
                            }
                        }
                    }
                @endphp

                @if(!empty($grid))
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle mb-0" style="min-width: 800px;">
                            <thead>
                                <tr class="bg-light">
                                    <th class="bg-light text-primary" style="width: 12%;">اليوم / الحصة</th>
                                    @for($i=1; $i<=$maxPeriod; $i++)
                                        <th class="bg-light text-muted">الحصة {{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($standardDays as $day)
                                    <tr>
                                        <td class="fw-bold bg-light">{{ $day }}</td>
                                        @for($i=1; $i<=$maxPeriod; $i++)
                                            @if(isset($grid[$day][$i]))
                                                @php $entry = $grid[$day][$i]; @endphp
                                                <td class="p-2" style="background-color: rgba(var(--bs-primary-rgb), 0.05);">
                                                    <div class="fw-bold text-primary" style="font-size: 0.9rem;">
                                                        {{ $entry->subject->name ?? '—' }}
                                                    </div>
                                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                        {{ $entry->section->schoolClass->name ?? '' }} - {{ $entry->section->name ?? '' }}
                                                    </div>
                                                </td>
                                            @else
                                                <td class="text-muted bg-light bg-opacity-50"></td>
                                            @endif
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-25 d-block"></i>
                        لا توجد جداول دراسية مسندة لهذا المعلم حالياً
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
