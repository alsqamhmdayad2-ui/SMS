@extends('layouts.app')
@section('title', 'لوحة التحكم الإدارية')

@section('content')

<x-page-header 
    title="لوحة التحكم الإدارية">
    <x-slot:actions>
        <button class='btn btn-secondary btn-sm'><i class='fas fa-download'></i> تصدير تقرير</button>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'لوحة التحكم']
]" />

<!-- Active Settings Banner -->
@if($activeYear && $activeSemester)
<div class="alert bg-sms-primary bg-opacity-10 border-0 border-start border-sms-primary border-4 d-flex align-items-center mb-4 p-3 shadow-sm rounded-end">
    <div class="rounded-circle bg-white text-sms-primary p-2 d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 48px; height: 48px;">
        <i class="fas fa-calendar-check fs-4"></i>
    </div>
    <div>
        <h6 class="mb-1 fw-bold text-sms-main">العام الدراسي النشط: {{ $activeYear->name }}</h6>
        <p class="mb-0 text-sms-muted small">
            الفصل الدراسي: <strong>{{ $activeSemester->name }}</strong>
            <span class="mx-2 text-muted">|</span>
            <a href="{{ route('admin.academic-years.index') }}" class="text-decoration-none">تغيير الإعدادات <i class="fas fa-arrow-left ms-1" style="font-size: 0.8em;"></i></a>
        </p>
    </div>
</div>
@else
<div class="alert alert-warning border-0 border-start border-warning border-4 d-flex align-items-center mb-4 p-3 shadow-sm rounded-end">
    <i class="fas fa-exclamation-triangle fs-3 text-warning me-3"></i>
    <div>
        <h6 class="mb-1 fw-bold text-dark">تنبيه: لا يوجد عام دراسي نشط!</h6>
        <p class="mb-0 text-muted small">
            يرجى <a href="{{ route('admin.academic-years.index') }}" class="fw-bold text-decoration-none">تفعيل عام دراسي وفصل دراسي</a> ليتمكن النظام من العمل بشكل صحيح.
        </p>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-4 hover-lift h-100 bg-white">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 mb-2">
                <i class="fas fa-user-plus fs-4"></i>
            </div>
            <span class="fw-bold">طالب جديد</span>
        </a>
    </div>
    <div class="col-12 col-md-3">
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-outline-success w-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-4 hover-lift h-100 bg-white">
            <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 mb-2">
                <i class="fas fa-chalkboard-teacher fs-4"></i>
            </div>
            <span class="fw-bold">معلم جديد</span>
        </a>
    </div>
    <div class="col-12 col-md-3">
        <a href="{{ route('admin.timetables.index') }}" class="btn btn-outline-info w-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-4 hover-lift h-100 bg-white">
            <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 mb-2">
                <i class="fas fa-calendar-alt fs-4"></i>
            </div>
            <span class="fw-bold">الجداول الدراسية</span>
        </a>
    </div>
    <div class="col-12 col-md-3">
        <a href="{{ route('admin.study-plans.index') }}" class="btn btn-outline-warning w-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-4 hover-lift h-100 bg-white">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 mb-2">
                <i class="fas fa-book-open fs-4"></i>
            </div>
            <span class="fw-bold">الخطة الدراسية</span>
        </a>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-4 mb-4 mt-1">
    <!-- إجمالي الطلاب -->
    <div class="col-12 col-sm-6 col-xl-3">
        <x-dashboard.stat-card 
            title="إجمالي الطلاب" 
            :value="$stats['students']" 
            icon="fas fa-user-graduate text-white" 
            color="primary" 
        />
    </div>

    <!-- أعضاء المعلمين -->
    <div class="col-12 col-sm-6 col-xl-3">
        <x-dashboard.stat-card 
            title="أعضاء المعلمين" 
            :value="$stats['teachers']" 
            icon="fas fa-chalkboard-teacher" 
            color="success" 
        />
    </div>

    <!-- أولياء الأمور -->
    <div class="col-12 col-sm-6 col-xl-3">
        <x-dashboard.stat-card 
            title="أولياء الأمور" 
            :value="$stats['parents']" 
            icon="fas fa-user-friends" 
            color="warning" 
        />
    </div>

    <!-- الشُعب الدراسية -->
    <div class="col-12 col-sm-6 col-xl-3">
        <x-dashboard.stat-card 
            title="الشُعب الدراسية" 
            :value="$stats['sections']" 
            icon="fas fa-school" 
            color="info" 
        />
    </div>
</div>

<!-- Charts and Tables -->
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="row g-4 h-100">
            <!-- Academic Structure Card -->
            <div class="col-12">
                <x-shared.card class="h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sitemap text-primary me-2"></i>الهيكل الأكاديمي</h6>
                        <span class="badge bg-primary rounded-pill">نشط</span>
                    </div>
                    <div class="p-4">
                        <div class="row text-center g-4">
                            <div class="col-4">
                                <a href="{{ route('admin.grades.index') }}" class="text-decoration-none d-block p-3 rounded-4 bg-light hover-shadow transition-all hover-lift">
                                    <i class="fas fa-layer-group fs-2 text-sms-primary mb-2"></i>
                                    <h3 class="fw-bold mb-1 text-dark">{{ $stats['grades'] }}</h3>
                                    <div class="small text-muted fw-semibold">مراحل دراسية</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('admin.classes.index') }}" class="text-decoration-none d-block p-3 rounded-4 bg-light hover-shadow transition-all hover-lift">
                                    <i class="fas fa-chalkboard fs-2 text-sms-success mb-2"></i>
                                    <h3 class="fw-bold mb-1 text-dark">{{ $stats['classes'] }}</h3>
                                    <div class="small text-muted fw-semibold">صفوف</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('admin.sections.index') }}" class="text-decoration-none d-block p-3 rounded-4 bg-light hover-shadow transition-all hover-lift">
                                    <i class="fas fa-school fs-2 text-sms-info mb-2"></i>
                                    <h3 class="fw-bold mb-1 text-dark">{{ $stats['sections'] }}</h3>
                                    <div class="small text-muted fw-semibold">شُعب</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </x-shared.card>
            </div>

            <!-- Today's Attendance Widget -->
            <div class="col-12">
                <x-shared.card class="border-0 shadow-sm rounded-4">
                    <div class="bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-success me-2"></i>حضور اليوم</h6>
                        <a href="{{ route('admin.attendance.reports.dashboard') }}" class="btn btn-sm btn-link text-decoration-none p-0">التفاصيل <i class="fas fa-arrow-left ms-1"></i></a>
                    </div>
                    <div class="p-4">
                        @if($todayStats && $todayStats['total_students'] > 0)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark">نسبة حضور الطلاب</span>
                                <span class="badge bg-success-subtle text-success fs-6">{{ $todayStats['attendance_percentage'] }}%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $todayStats['attendance_percentage'] }}%" aria-valuenow="{{ $todayStats['attendance_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small text-muted">
                                <span>حاضر: <strong class="text-success">{{ $todayStats['present_count'] }}</strong></span>
                                <span>غائب: <strong class="text-danger">{{ $todayStats['absent_count'] }}</strong></span>
                                <span>متأخر: <strong class="text-warning">{{ $todayStats['late_count'] }}</strong></span>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-check-double fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">لم يتم تسجيل أي حضور لليوم بعد.</p>
                            </div>
                        @endif
                    </div>
                </x-shared.card>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <x-shared.card class="h-100 border-0 shadow-sm rounded-4">
            <x-slot:header>
                <div class="d-flex justify-content-between align-items-center w-100 pb-2 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-plus me-2 text-primary"></i>آخر الطلاب المسجلين
                    </h5>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-primary-subtle text-primary rounded-pill px-3 fw-semibold transition-all hover-lift">
                        عرض الكل <i class="fas fa-arrow-left ms-1" style="font-size: 0.8em;"></i>
                    </a>
                </div>
            </x-slot:header>
            
            <div class="activity-list mt-3">
                @forelse($recentStudents as $student)
                <div class="activity-item d-flex align-items-center gap-3 p-3 mb-2 rounded-3 bg-light border border-white hover-shadow transition-all">
                    <div class="icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width:45px;height:45px;">
                        {{ mb_substr($student->first_name, 0, 1) }}
                    </div>
                    <div class="details flex-grow-1">
                        <p class="mb-1 fw-bold text-dark">{{ $student->first_name }} {{ $student->family_name }}</p>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="far fa-clock me-1"></i> {{ $student->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-icon btn-sm btn-light text-secondary rounded-circle">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-user-slash text-muted fs-3"></i>
                    </div>
                    <p class="text-muted fw-semibold">لا يوجد طلاب حتى الآن</p>
                </div>
                @endforelse
            </div>
        </x-shared.card>
    </div>
    
    @push('scripts')
    <style>
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.05)!important; }
        .hover-shadow:hover { box-shadow: 0 .25rem .5rem rgba(0,0,0,.05); background-color: #fff !important; border-color: #e9ecef !important;}
        .btn-primary-subtle { background-color: rgba(13, 110, 253, 0.1); border: none; }
        .btn-primary-subtle:hover { background-color: rgba(13, 110, 253, 0.2); }
    </style>
    @endpush
</div>

@endsection


