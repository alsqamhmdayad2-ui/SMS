@extends('layouts.app')
@section('title', 'إضافة فصل دراسي')

@section('content')

<x-page-header 
    title="إضافة فصل دراسي">
    <x-slot:actions>
        <a href="{{ route('admin.semesters.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الفصول الدراسية', 'url' => route('admin.semesters.index')],
    ['name' => 'إضافة فصل']
]" />

<x-shared.card shadow="sm" class="mb-4">
    <form action="{{ route('admin.semesters.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <x-form.select name="academic_year_id" label="العام الأكاديمي" required="true" :error="$errors->first('academic_year_id')">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-6">
                <x-form.input name="name" label="اسم الفصل" required="true" placeholder="مثال: الفصل الدراسي الأول" :error="$errors->first('name')" />
            </div>
            
            <div class="col-md-4">
                <x-form.input type="date" name="start_date" label="تاريخ البدء" required="true" :error="$errors->first('start_date')" />
            </div>
            <div class="col-md-4">
                <x-form.input type="date" name="end_date" label="تاريخ الانتهاء" required="true" :error="$errors->first('end_date')" />
            </div>
            <div class="col-md-4">
                <x-form.select name="status" label="حالة الفصل" required="true" :error="$errors->first('status')">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                </x-form.select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.semesters.index') }}" class="btn btn-light px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ الفصل</button>
        </div>
    </form>
</x-shared.card>

@endsection
