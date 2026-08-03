@extends('layouts.app')
@section('title', 'تعديل مرحلة دراسية')

@section('content')

<x-page-header 
    title="تعديل مرحلة: {{ $grade->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.grades.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المراحل الدراسية', 'url' => route('admin.grades.index')],
    ['name' => 'تعديل مرحلة']
]" />

<form action="{{ route('admin.grades.update', $grade->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h3><i class="fas fa-edit" style="margin-inline-start: 8px; color: var(--info)"></i>تعديل بيانات المرحلة الدراسية</h3>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <x-form.input name="name" label="اسم المرحلة" value="{{ $grade->name }}" required="true" :error="$errors->first('name')" />
                </div>
                <div class="col-md-12">
                    <x-form.textarea name="description" label="ملاحظات إضافية" value="{{ $grade->description }}" rows="4" :error="$errors->first('description')" />
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-secondary px-4">
            <i class="fas fa-save"></i> <span>تحديث البيانات</span>
        </button>
        <a href="{{ route('admin.grades.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
    </div>
</form>

@endsection
