@extends('layouts.app')
@section('title', 'الملف الشخصي - ولي الأمر')

@section('content')

<x-page-header title="الملف الشخصي">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'الملف الشخصي']
]" />

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100 slide-up">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-user-shield ms-2 text-primary"></i>المعلومات الشخصية</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">اسم الأب</small>
                            <span class="fw-bold text-dark">{{ $parent->father_name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">اسم الأم</small>
                            <span class="fw-bold text-dark">{{ $parent->mother_name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">البريد الإلكتروني</small>
                            <span class="fw-bold text-dark">{{ $parent->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">رقم الهاتف</small>
                            <span class="fw-bold text-dark" style="direction:ltr;display:inline-block;">{{ $parent->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">المهنة</small>
                            <span class="fw-bold text-dark">{{ $parent->occupation ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle">
                            <small class="text-muted d-block mb-1">العنوان</small>
                            <span class="fw-bold text-dark">{{ $parent->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100 slide-up" style="animation-delay: 0.2s;">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-users ms-2 text-info"></i>الأبناء المسجلين</h5>
            </div>
            <div class="card-body">
                @if($parent && $parent->students)
                <ul class="list-group list-group-flush">
                    @foreach($parent->students as $child)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                        <div>
                            <i class="fas fa-user-graduate ms-2 text-primary"></i>
                            <span class="fw-bold">{{ $child->name }}</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $child->schoolClass->name ?? '-' }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted text-center">لا يوجد أبناء مسجلين</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
