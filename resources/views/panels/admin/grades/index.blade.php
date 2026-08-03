@extends('layouts.app')
@section('title', 'قائمة المراحل الدراسية')

@section('content')

<x-page-header 
    title="المراحل الدراسية">
    <x-slot:actions>
        <a href="{{ route('admin.grades.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة مرحلة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'المراحل الدراسية']
]" />

<div class="row g-4 mt-2">
    @forelse($grades as $grade)
        @php
            // Assign a specific icon and color based on the grade name
            $icon = 'fas fa-layer-group';
            $color = 'primary';
            
            if (str_contains($grade->name, 'ابتدائي')) {
                $icon = 'fas fa-child';
                $color = 'primary';
            } elseif (str_contains($grade->name, 'إعدادي')) {
                $icon = 'fas fa-user-graduate';
                $color = 'success';
            } elseif (str_contains($grade->name, 'ثانوي')) {
                $icon = 'fas fa-university';
                $color = 'info';
            }
        @endphp
        <div class="col-md-6">
            <div class="card h-100 border-start border-{{ $color }} border-4 shadow-sm" style="transition: var(--transition);">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 mb-0" style="font-weight: 700; color: var(--{{ $color }});">{{ $grade->name }}</h3>
                            <div class="icon-wrap p-2 rounded bg-light text-{{ $color }}" style="font-size: 1.25rem;">
                                <i class="{{ $icon }}"></i>
                            </div>
                        </div>
                        <p class="text-muted small">{{ $grade->description ?? 'لا يوجد وصف متاح لهذه المرحلة.' }}</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <span class="badge bg-light text-{{ $color }} px-3 py-2" style="font-size: 0.85rem; border-radius: var(--border-radius-sm);">{{ $grade->classes()->count() ?? 0 }} صفوف دراسية</span>
                        <div class="action-btns d-flex gap-2">
                            <a href="{{ route('admin.classes.create', ['grade_id' => $grade->id]) }}" class="btn btn-sm btn-{{ $color }} text-white" title="إضافة صفوف للمرحلة"><i class="fas fa-plus me-1"></i> إضافة صفوف</a>
                            <a href="{{ route('admin.grades.edit', $grade->id) }}" class="btn btn-light btn-sm text-primary" title="تعديل المرحلة"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.grades.destroy', $grade->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المرحلة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light btn-sm text-danger" title="حذف المرحلة"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">لا توجد مراحل دراسية مضافة حتى الآن.</div>
        </div>
    @endforelse
</div>

@if(method_exists($grades, 'hasPages') && $grades->hasPages())
    <div class="mt-4">
        {{ $grades->links() }}
    </div>
@endif

@endsection
