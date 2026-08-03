@extends('layouts.app')
@section('title', 'إنشاء اختبار جديد')

@section('content')

<x-page-header 
    title="إنشاء اختبار جديد">
    <x-slot:actions>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للقائمة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الاختبارات', 'url' => route('admin.exams.index')],
    ['name' => 'إنشاء اختبار']
]" />

<x-shared.card shadow="sm" class="mb-4">
    <form action="{{ route('admin.exams.store') }}" method="POST">
        @csrf

        <!-- القسم 1: معلومات الاختبار -->
        <h5 class="fw-bold mb-3 text-sms-primary border-bottom pb-2"><i class="fas fa-file-alt me-2"></i>معلومات الاختبار</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <x-form.input name="title" label="عنوان الاختبار" required="true" placeholder="مثال: اختبار منتصف الفصل - رياضيات" :error="$errors->first('title')" />
            </div>
            <div class="col-md-6">
                <x-form.select name="type" label="نوع الاختبار" required="true" :error="$errors->first('type')">
                    <option value="">-- اختر النوع --</option>
                    <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>اختبار قصير (Quiz)</option>
                    <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>اختبار شهري</option>
                    <option value="midterm" {{ old('type') == 'midterm' ? 'selected' : '' }}>اختبار منتصف الفصل</option>
                    <option value="final" {{ old('type') == 'final' ? 'selected' : '' }}>الاختبار النهائي</option>
                </x-form.select>
            </div>
        </div>

        <!-- القسم 2: المعلومات الأكاديمية -->
        <h5 class="fw-bold mb-3 text-sms-primary border-bottom pb-2"><i class="fas fa-graduation-cap me-2"></i>المعلومات الأكاديمية</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <x-form.select name="academic_year_id" label="العام الدراسي" required="true" :error="$errors->first('academic_year_id')">
                    <option value="">-- اختر --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="semester_id" label="الفصل الدراسي" required="true" :error="$errors->first('semester_id')">
                    <option value="">-- اختر --</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="grade_id" label="المرحلة" required="true" :error="$errors->first('grade_id')">
                    <option value="">-- اختر --</option>
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="class_id" label="الصف" required="true" :error="$errors->first('class_id')">
                    <option value="">-- اختر --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-4">
                <x-form.select name="section_id" label="الشعبة" required="true" :error="$errors->first('section_id')">
                    <option value="">-- اختر --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-4">
                <x-form.select name="subject_id" label="المادة" required="true" :error="$errors->first('subject_id')">
                    <option value="">-- اختر المادة --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-4">
                <x-form.select name="teacher_id" label="المعلم" required="true" :error="$errors->first('teacher_id')">
                    <option value="">-- اختر المعلم --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>

        <!-- القسم 3: الجدول والتفاصيل -->
        <h5 class="fw-bold mb-3 text-sms-primary border-bottom pb-2"><i class="fas fa-calendar-alt me-2"></i>الجدول والتفاصيل</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <x-form.input type="date" name="exam_date" label="تاريخ الاختبار" required="true" :error="$errors->first('exam_date')" />
            </div>
            <div class="col-md-2">
                <x-form.input type="time" name="start_time" label="وقت البدء" required="true" :error="$errors->first('start_time')" />
            </div>
            <div class="col-md-2">
                <x-form.input type="time" name="end_time" label="وقت الانتهاء" required="true" :error="$errors->first('end_time')" />
            </div>
            <div class="col-md-2">
                <x-form.input type="number" name="duration_minutes" label="المدة (دقيقة)" placeholder="90" :error="$errors->first('duration_minutes')" />
            </div>
            <div class="col-md-3">
                <x-form.select name="status" label="الحالة" required="true" :error="$errors->first('status')">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ old('status', 'draft') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-12">
                <x-form.textarea name="instructions" label="تعليمات الاختبار (اختياري)" rows="3" placeholder="اقرأ جميع الأسئلة بتمعن..." :error="$errors->first('instructions')" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.exams.index') }}" class="btn btn-light px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>حفظ والانتقال لمنشئ الأسئلة
            </button>
        </div>
    </form>
</x-shared.card>

@endsection
