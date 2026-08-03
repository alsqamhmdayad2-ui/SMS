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

<!-- Stats Cards Row -->
<div class="row g-4 mb-4 mt-1">
    <!-- إجمالي الطلاب -->
    <div class="col-12 col-sm-6 col-xl-3">
        <x-dashboard.stat-card 
            title="إجمالي الطلاب" 
            :value="$stats['students']" 
            icon="fas fa-user-graduate" 
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
        <x-shared.card title="الهيكل الأكاديمي" class="h-100">
            <div class="row text-center g-3 py-2">
                <div class="col-4">
                    <div class="fw-bold fs-3 text-sms-primary">{{ $stats['grades'] }}</div>
                    <div class="small text-sms-muted">المراحل الدراسية</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-3 text-sms-success">{{ $stats['classes'] }}</div>
                    <div class="small text-sms-muted">الصفوف</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-3 text-sms-info">{{ $stats['sections'] }}</div>
                    <div class="small text-sms-muted">الشُعب</div>
                </div>
            </div>
            <hr class="my-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.grades.index') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-layer-group me-1"></i>المراحل</a>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-chalkboard me-1"></i>الصفوف</a>
                <a href="{{ route('admin.sections.index') }}" class="btn btn-sm btn-outline-info"><i class="fas fa-school me-1"></i>الشُعب</a>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-book me-1"></i>المواد</a>
            </div>
        </x-shared.card>
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


