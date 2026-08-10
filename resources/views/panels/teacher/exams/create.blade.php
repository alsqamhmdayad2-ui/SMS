@extends('layouts.app')
@section('title', 'إنشاء اختبار جديد')

@section('content')

<x-page-header title="إنشاء اختبار جديد">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي', 'url' => route('teacher.exams.index')],
    ['name' => 'اختبار جديد']
]" />

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-plus-circle text-primary me-2"></i>بيانات الاختبار</h5>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form action="{{ route('teacher.exams.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">عنوان الاختبار <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="مثال: اختبار الفصل الأول - رياضيات" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">نوع الاختبار <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- اختر النوع --</option>
                                <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>اختبار قصير (Quiz)</option>
                                <option value="midterm" {{ old('type') == 'midterm' ? 'selected' : '' }}>نصف فصلي (Midterm)</option>
                                <option value="final" {{ old('type') == 'final' ? 'selected' : '' }}>نهائي (Final)</option>
                                <option value="assignment" {{ old('type') == 'assignment' ? 'selected' : '' }}>واجب (Assignment)</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المادة <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">-- اختر المادة --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الشعبة <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
                                <option value="">-- اختر الشعبة --</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->schoolClass?->name }} - {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">العام الدراسي <span class="text-danger">*</span></label>
                            <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                <option value="">-- اختر العام --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الفصل الدراسي <span class="text-danger">*</span></label>
                            <select name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                <option value="">-- اختر الفصل --</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تاريخ الاختبار</label>
                            <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror"
                                   value="{{ old('exam_date') }}">
                            @error('exam_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت البداية</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">وقت النهاية</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المدة (بالدقائق)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes') }}" min="1" placeholder="60">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الدرجة الكلية <span class="text-danger">*</span></label>
                            <input type="number" name="total_marks" class="form-control @error('total_marks') is-invalid @enderror" value="{{ old('total_marks') }}" min="1" required>
                            @error('total_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">طريقة عرض الاختبار للطالب</label>
                            <select name="display_mode" class="form-select">
                                <option value="single_page" {{ old('display_mode') === 'single_page' ? 'selected' : '' }}>عرض جميع الأسئلة في صفحة واحدة</option>
                                <option value="per_question" {{ old('display_mode') === 'per_question' ? 'selected' : '' }}>عرض سؤال واحد في كل مرة (التالي/السابق)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">تعليمات الاختبار</label>
                            <textarea name="instructions" class="form-control" rows="3"
                                      placeholder="أي تعليمات للطلاب...">{{ old('instructions') }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> إنشاء الاختبار
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
