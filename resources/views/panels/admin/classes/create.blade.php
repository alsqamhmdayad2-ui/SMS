@extends('layouts.app')
@section('title', 'إضافة صف دراسي')

@section('content')

<x-page-header 
    title="إضافة صف دراسي">
    <x-slot:actions>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الصفوف الدراسية', 'url' => route('admin.classes.index')],
    ['name' => 'إضافة صف']
]" />

<x-shared.card shadow="sm" class="mb-4">
    <form action="{{ route('admin.classes.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <x-form.input name="name" label="اسم الصف" required="true" placeholder="مثال: الصف الأول" :error="$errors->first('name')" />
            </div>
            <div class="col-md-3">
                <x-form.select name="grade_id" label="المرحلة الدراسية" required="true" :error="$errors->first('grade_id')">
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" {{ (old('grade_id') ?? request('grade_id')) == $grade->id ? 'selected' : '' }}>
                            {{ $grade->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="academic_year_id" label="العام الأكاديمي" required="true" :error="$errors->first('academic_year_id')">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="status" label="حالة الصف" required="true" :error="$errors->first('status')">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                </x-form.select>
            </div>
            <div class="col-md-12">
                <x-form.textarea name="description" label="وصف الصف (اختياري)" rows="2" placeholder="معلومات إضافية عن الصف..." :error="$errors->first('description')" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.classes.index') }}" class="btn btn-light px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ الصف</button>
        </div>
    </form>
</x-shared.card>

@endsection
