@extends('layouts.app')
@section('title', 'تعديل الاختبار — ' . $exam->title)

@section('content')

<x-page-header title="تعديل الاختبار">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي', 'url' => route('teacher.exams.index')],
    ['name' => $exam->title, 'url' => route('teacher.exams.show', $exam)],
    ['name' => 'تعديل']
]" />

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-edit text-warning me-2"></i>تعديل: {{ $exam->title }}</h5>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form action="{{ route('teacher.exams.update', $exam) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">عنوان الاختبار <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $exam->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">نوع الاختبار <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="quiz"       {{ old('type', $exam->type) == 'quiz' ? 'selected' : '' }}>اختبار قصير</option>
                                <option value="midterm"    {{ old('type', $exam->type) == 'midterm' ? 'selected' : '' }}>نصف فصلي</option>
                                <option value="final"      {{ old('type', $exam->type) == 'final' ? 'selected' : '' }}>نهائي</option>
                                <option value="assignment" {{ old('type', $exam->type) == 'assignment' ? 'selected' : '' }}>واجب</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تاريخ الاختبار</label>
                            <input type="date" name="exam_date" class="form-control"
                                   value="{{ old('exam_date', $exam->exam_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت البداية</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $exam->start_time) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت النهاية</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $exam->end_time) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">المدة (دقائق)</label>
                            <input type="number" name="duration_minutes" class="form-control" min="5"
                                   value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">تعليمات الاختبار</label>
                            <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $exam->instructions) }}</textarea>
                        </div>

                        {{-- Read-only info --}}
                        <div class="col-12">
                            <div class="alert alert-light border mb-0">
                                <small class="text-muted">
                                    <strong>المادة:</strong> {{ $exam->subject?->name }} &nbsp;|&nbsp;
                                    <strong>الشعبة:</strong> {{ $exam->section?->schoolClass?->name }} - {{ $exam->section?->name }}
                                    <br><em>لتغيير المادة أو الشعبة، احذف الاختبار وأنشئ اختباراً جديداً.</em>
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="fas fa-save me-1"></i> حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
