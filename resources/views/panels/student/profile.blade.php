@extends('layouts.app')
@section('title', 'الملف الشخصي - الطالب')

@section('content')

<x-page-header title="الملف الشخصي">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الملف الشخصي']
]" />

<div class="row g-4">
    <!-- المعلومات الشخصية -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100 slide-up">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-user ms-2 text-primary"></i>المعلومات الشخصية</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">الاسم الكامل</small>
                            <span class="fw-bold text-dark">{{ $student->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">رقم الطالب</small>
                            <span class="fw-bold text-dark" style="direction:ltr;display:inline-block;">{{ $student->id ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">البريد الإلكتروني</small>
                            <span class="fw-bold text-dark">{{ $student->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">رقم الهاتف</small>
                            <span class="fw-bold text-dark" style="direction:ltr;display:inline-block;">{{ $student->phone ?: ($student->user->phone ?? ($student->parent->phone ?? '-')) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-10">
                            <small class="text-primary d-block mb-1 fw-bold">ولي الأمر</small>
                            <span class="fw-bold text-primary">{{ $student->parent ? ($student->parent->full_name ?: ($student->parent->user->name ?? '-')) : '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">تاريخ الميلاد</small>
                            <span class="fw-bold text-dark">{{ $student->birth_date ? $student->birth_date->format('Y-m-d') : '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">النوع</small>
                            <span class="fw-bold text-dark">{{ $student->gender === 'Male' ? 'ذكر' : ($student->gender === 'Female' ? 'أنثى' : ($student->gender ?? '-')) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">العنوان</small>
                            <span class="fw-bold text-dark">{{ $student->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- المعلومات الأكاديمية -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100 slide-up" style="animation-delay: 0.2s;">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-graduation-cap ms-2 text-success"></i>المعلومات الأكاديمية</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">المرحلة الدراسية</span>
                        <span class="fw-bold">{{ $student->grade->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">الصف</span>
                        <span class="fw-bold">{{ $student->schoolClass->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">الشعبة</span>
                        <span class="fw-bold">{{ $student->section->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">السنة الدراسية</span>
                        <span class="fw-bold">{{ \App\Models\AcademicYear::where('status', 1)->first()->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">نسبة الحضور</span>
                        <span class="badge bg-success px-3 py-2">95%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <span class="text-muted">متوسط الدرجات</span>
                        <span class="badge bg-primary px-3 py-2">87%</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
