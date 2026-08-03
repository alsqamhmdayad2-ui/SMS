@extends('layouts.app')
@section('title', 'بيانات الأبناء - ولي الأمر')

@section('content')

<x-page-header title="بيانات الأبناء">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'بيانات الأبناء']
]" />

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    @forelse($children as $child)
    <div class="col">
        <div class="card h-100 shadow-sm border-0 slide-up" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body text-center p-4">
                <div class="profile-avatar mb-3" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto; background: var(--gradient-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                
                <h4 class="fw-bold mb-1">{{ $child->name }}</h4>
                <p class="text-muted mb-3">
                    {{ $child->grade->name ?? 'غير محدد' }} - 
                    {{ $child->schoolClass->name ?? '' }} 
                    ({{ $child->section->name ?? '' }})
                </p>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> منتظم</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('parent.child.profile', $child->id) }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-id-card me-2"></i> عرض الملف
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('parent.results', ['student_id' => $child->id]) }}" class="btn btn-light flex-grow-1 border">
                            <i class="fas fa-chart-bar me-1 text-primary"></i> الدرجات
                        </a>
                        <a href="{{ route('parent.attendance', ['student_id' => $child->id]) }}" class="btn btn-light flex-grow-1 border">
                            <i class="fas fa-calendar-check me-1 text-success"></i> الحضور
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <x-empty-state message="لا يوجد أبناء مسجلين حالياً" icon="fas fa-users" />
    </div>
    @endforelse
</div>

@endsection
