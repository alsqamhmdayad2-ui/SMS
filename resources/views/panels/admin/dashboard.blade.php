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
        <x-shared.card class="h-100">
            <x-slot:header>
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h5 class="mb-0 fw-bold">آخر الطلاب المسجلين</h5>
                    <a href="{{ route('admin.students.index') }}" class="text-decoration-none small">عرض الكل</a>
                </div>
            </x-slot:header>
            
            <div class="activity-list">
                @forelse($recentStudents as $student)
                <div class="activity-item d-flex gap-3 mb-3">
                    <div class="icon rounded-circle bg-sms-primary bg-opacity-10 text-sms-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                        {{ mb_substr($student->first_name, 0, 1) }}
                    </div>
                    <div class="details">
                        <p class="mb-0 small fw-bold">{{ $student->first_name }} {{ $student->family_name }}</p>
                        <small class="text-sms-muted">{{ $student->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <p class="text-center text-sms-muted py-3">لا يوجد طلاب حتى الآن</p>
                @endforelse
            </div>
        </x-shared.card>
    </div>
</div>

@endsection


