@extends('layouts.app')
@section('title', 'الخطة الدراسية')

@section('content')

<x-page-header title="الخطة الدراسية (توزيع الحصص)">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المناهج', 'url' => '#'],
    ['name' => 'الخطة الدراسية']
]" />

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.study-plans.index') }}" id="classForm">
            <div class="row align-items-end g-3">
                <div class="col-md-5">
                    <label for="class_id" class="form-label small fw-bold">اختر الصف الدراسي <span class="text-danger">*</span></label>
                    <select class="form-select" name="class_id" id="class_id" onchange="document.getElementById('classForm').submit()">
                        <option value="">-- يرجى اختيار الصف --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ ($selectedClass && $selectedClass->id == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->grade->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedClass)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-book-open me-2"></i> الخطة الدراسية لـ: {{ $selectedClass->name }}</h6>
            <small class="text-muted">قم بتحديد عدد الحصص الأسبوعية لكل مادة تُدرَّس في هذا الصف وفقاً لخطة وزارة التربية والتعليم.</small>
        </div>
        <div class="card-body p-4">
            @if($subjects->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">لم يتم ربط أي مواد دراسية بهذا الصف بعد.</p>
                    <a href="{{ route('admin.subjects.create') }}" class="btn btn-outline-primary btn-sm mt-3">إضافة مواد جديدة للصف</a>
                </div>
            @else
                <form action="{{ route('admin.study-plans.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%;">المادة الدراسية</th>
                                    <th>عدد الحصص الأسبوعية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td class="text-start fw-bold">
                                            <i class="fas fa-book text-muted me-2"></i> {{ $subject->name }}
                                        </td>
                                        <td>
                                            <div class="input-group mx-auto" style="max-width: 150px;">
                                                <input type="number" class="form-control text-center fw-bold" 
                                                       name="weekly_periods[{{ $subject->id }}]" 
                                                       value="{{ old('weekly_periods.'.$subject->id, $subject->pivot->weekly_periods) }}" 
                                                       min="0" max="40" required>
                                                <span class="input-group-text">حصة</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ الخطة الدراسية</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

@endsection
