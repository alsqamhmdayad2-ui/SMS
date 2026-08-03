@extends('layouts.app')
@section('title', 'تعديل مادة: {{ $subject->name }}')

@section('content')

<x-page-header title="تعديل مادة: {{ $subject->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.subjects.show', $subject->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> عرض</a>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المواد الدراسية', 'url' => route('admin.subjects.index')],
    ['name' => 'تعديل مادة']
]" />

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="name" class="form-label small fw-bold">اسم المادة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $subject->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label small fw-bold">رمز المادة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $subject->code) }}" required dir="ltr">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label small fw-bold">حالة المادة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1" {{ old('status', $subject->status) == '1' ? 'selected' : '' }}>نشطة</option>
                        <option value="0" {{ old('status', $subject->status) == '0' ? 'selected' : '' }}>غير نشطة</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label small fw-bold">وصف المادة (اختياري)</label>
                    <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $subject->description) }}</textarea>
                </div>
            </div>

            <!-- Class Assignment -->
            <hr>
            <h6 class="fw-bold mb-3"><i class="fas fa-chalkboard text-primary me-2"></i> ربط المادة بالصفوف</h6>
            <p class="text-muted small mb-3">اختر الصفوف التي تُدرَّس فيها هذه المادة (<strong>تغيير هذه القائمة سيحذف تعيينات المعلمين المرتبطة بالصفوف المُزالة</strong>):</p>

            <div class="row g-2 mb-4">
                @foreach($grades as $grade)
                    <div class="col-12 mb-2">
                        <div class="fw-semibold text-primary mb-1 small"><i class="fas fa-layer-group me-1"></i> {{ $grade->name }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($classes->where('grade_id', $grade->id) as $class)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="class_ids[]" 
                                           id="class_{{ $class->id }}" value="{{ $class->id }}"
                                           {{ in_array($class->id, old('class_ids', $assignedClassIds)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-light px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

@endsection
