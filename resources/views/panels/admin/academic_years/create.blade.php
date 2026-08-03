@extends('layouts.app')

@section('title', 'إضافة عام أكاديمي')

@section('content')

<x-page-header 
    title="إضافة عام أكاديمي">
    <x-slot:actions>
        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الأعوام الأكاديمية', 'url' => route('admin.academic-years.index')],
    ['name' => 'إضافة عام أكاديمي']
]" />

<x-shared.card shadow="sm" class="mb-4">
    <form action="{{ route('admin.academic-years.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <x-form.input name="name" label="اسم العام الأكاديمي (مثال: 2025 / 2026)" required="true" :error="$errors->first('name')" />
            </div>
            
            <div class="col-md-4">
                <x-form.input type="date" name="start_date" label="تاريخ البدء" required="true" :error="$errors->first('start_date')" />
            </div>
            <div class="col-md-4">
                <x-form.input type="date" name="end_date" label="تاريخ الانتهاء" required="true" :error="$errors->first('end_date')" />
            </div>
            <div class="col-md-4">
                <x-form.select name="status" label="الحالة" required="true" :error="$errors->first('status')">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                </x-form.select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ</button>
        </div>
    </form>
</x-shared.card>

@endsection
