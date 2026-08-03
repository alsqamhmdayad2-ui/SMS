@extends('layouts.app')

@section('title', 'تعديل عام أكاديمي')

@section('content')

<x-page-header 
    title="تعديل عام أكاديمي: {{ $academicYear->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الأعوام الأكاديمية', 'url' => route('admin.academic-years.index')],
    ['name' => 'تعديل عام أكاديمي']
]" />

<x-shared.card shadow="sm" class="mb-4">
    <form action="{{ route('admin.academic-years.update', $academicYear->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <x-form.input name="name" label="اسم العام الأكاديمي (مثال: 2025 / 2026)" value="{{ $academicYear->name }}" required="true" :error="$errors->first('name')" />
            </div>
            
            <div class="col-md-4">
                <x-form.input type="date" name="start_date" label="تاريخ البدء" value="{{ $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : '' }}" required="true" :error="$errors->first('start_date')" />
            </div>
            <div class="col-md-4">
                <x-form.input type="date" name="end_date" label="تاريخ الانتهاء" value="{{ $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : '' }}" required="true" :error="$errors->first('end_date')" />
            </div>
            <div class="col-md-4">
                <x-form.select name="status" label="الحالة" required="true" :error="$errors->first('status')">
                    <option value="1" {{ old('status', $academicYear->status) == '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('status', $academicYear->status) == '0' ? 'selected' : '' }}>غير نشط</option>
                </x-form.select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ التعديلات</button>
        </div>
    </form>
</x-shared.card>

@endsection
