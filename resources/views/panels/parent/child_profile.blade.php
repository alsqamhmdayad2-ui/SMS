@extends('layouts.app')
@section('title', 'ملف الابن - ' . $student->name)

@section('content')

<x-page-header title="ملف الابن: {{ $student->name }}">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'بيانات الأبناء', 'url' => route('parent.children')],
    ['name' => $student->name]
]" />

<div class="row g-4">
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card shadow-sm border-0 h-100 slide-up text-center pt-4">
            <div class="card-body">
                <div class="profile-avatar mb-3 mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem; background: var(--gradient-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h4 class="fw-bold">{{ $student->name }}</h4>
                <div class="mt-2">
                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> منتظم</span>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pb-4">
                <div class="d-grid gap-2">
                    <a href="{{ route('parent.results', ['student_id' => $student->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar me-2"></i> عرض الدرجات
                    </a>
                    <a href="{{ route('parent.attendance', ['student_id' => $student->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-calendar-check me-2"></i> سجل الحضور
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8 col-lg-9">
        <div class="card shadow-sm border-0 h-100 slide-up" style="animation-delay: 0.1s;">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-id-card ms-2 text-primary"></i>البيانات الأكاديمية والشخصية</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">رقم الطالب (Roll Number)</small>
                            <span class="fw-bold text-dark fs-5" style="direction:ltr;display:inline-block;">{{ $student->id }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">المرحلة الدراسية</small>
                            <span class="fw-bold text-dark fs-5">{{ $student->grade->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">الصف الدراسي (Class)</small>
                            <span class="fw-bold text-dark fs-5">{{ $student->schoolClass->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">الشعبة (Section)</small>
                            <span class="fw-bold text-dark fs-5">{{ $student->section->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">البريد الإلكتروني</small>
                            <span class="fw-bold text-dark fs-5">{{ $student->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                            <small class="text-muted d-block mb-1">تاريخ الميلاد</small>
                            <span class="fw-bold text-dark fs-5">{{ $student->birth_date ? $student->birth_date->format('Y-m-d') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
