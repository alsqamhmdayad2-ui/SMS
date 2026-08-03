@extends('layouts.app')
@section('title', 'تعديل شعبة دراسية')

@section('content')

<x-page-header 
    title="تعديل شعبة: {{ $section->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الشُعب الدراسية', 'url' => route('admin.sections.index')],
    ['name' => 'تعديل شعبة']
]" />

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.sections.update', $section->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="name" class="form-label small fw-bold">اسم الشعبة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $section->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label for="class_id" class="form-label small fw-bold">الصف التابع <span class="text-danger">*</span></label>
                    <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                        <option value="">-- اختر الصف --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $section->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->grade->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="capacity" class="form-label small fw-bold">الطاقة الاستيعابية <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $section->capacity) }}" min="1" required>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label small fw-bold">حالة الشعبة <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="1" {{ old('status', $section->status) == '1' ? 'selected' : '' }}>نشطة</option>
                        <option value="0" {{ old('status', $section->status) == '0' ? 'selected' : '' }}>غير نشطة</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.sections.index') }}" class="btn btn-light px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

@endsection
