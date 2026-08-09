@extends('layouts.app')
@section('title', 'إدخال الدرجات')

@section('content')

<x-page-header title="إدخال الدرجات">
    @if($exam ?? false)
    <x-slot name="actions">
        <div id="saveIndicator" class="d-flex align-items-center gap-2" style="display:none!important;">
            <span class="badge bg-success" id="statusSaved" style="display:none;"><i class="fas fa-check-circle"></i> تم الحفظ</span>
            <span class="badge bg-warning text-dark" id="statusSaving" style="display:none;"><i class="fas fa-sync fa-spin"></i> جاري الحفظ...</span>
            <span class="badge bg-danger" id="statusError" style="display:none;"><i class="fas fa-times-circle"></i> خطأ</span>
        </div>
    </x-slot>
    @endif
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'إدخال الدرجات']
]" />

<div class="">

    <x-alerts />

    <!-- Filters Card -->
    <x-shared.card class="mb-4 bg-light" shadow="sm">
        <form action="{{ route('admin.marks-entry.index') }}" method="GET" id="filtersForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="academic_year_id" id="filterYear" label="العام الدراسي" required="true">
                        <option value="">اختر...</option>
                        @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="semester_id" id="filterSemester" label="الفصل الدراسي">
                        <option value="">الكل</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="grade_id" id="filterGrade" label="المرحلة" required="true" data-url="{{ route('admin.marks-entry.get-classes') }}">
                        <option value="">اختر...</option>
                        @foreach($grades as $g)
                        <option value="{{ $g->id }}" {{ request('grade_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="class_id" id="filterClass" label="الصف" required="true" data-url="{{ route('admin.marks-entry.get-sections') }}">
                        <option value="">اختر...</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="section_id" id="filterSection" label="الشعبة" required="true">
                        <option value="">اختر...</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="subject_id" id="filterSubject" label="المادة" required="true">
                        <option value="">اختر...</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 flex-grow-1">
                    <x-form.select name="exam_id" id="filterExam" label="الاختبار" required="true">
                        <option value="">اختر...</option>
                        @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ request('exam_id') == $e->id ? 'selected' : '' }}>{{ $e->title }} ({{ ucfirst($e->type) }})</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-auto mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> عرض</button>
                </div>
        </form>
    </x-shared.card>

    @if($exam)
    <!-- Exam Info Bar -->
    <x-shared.card class="mb-3 border-start border-sms-primary border-4" shadow="sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $exam->title }}</strong> &mdash; {{ $exam->subject->name ?? '' }} |
                الدرجة الكلية: <x-shared.badge type="primary" class="fs-6">{{ $exam->total_marks }}</x-shared.badge>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" id="btnAllPresent">الكل حاضر</button>
                <button class="btn btn-sm btn-outline-danger" id="btnAllAbsent">الكل غائب</button>
                <button class="btn btn-sm btn-outline-warning" id="btnSaveAll"><i class="fas fa-save"></i> حفظ الكل</button>
            </div>
        </div>
    </x-shared.card>

    <!-- Data Grid -->
    <x-shared.card shadow="sm">
        <x-table.data-table hover="true" id="marksTable">
            <x-slot:header>
                <th style="width:40px">#</th>
                <th>اسم الطالب</th>
                <th style="width:130px">الحالة</th>
                <th style="width:120px">الدرجة / {{ $exam->total_marks }}</th>
                <th style="width:80px">%</th>
                <th style="width:80px">التقدير</th>
                <th style="width:180px">ملاحظات</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($students as $index => $student)
                @php
                    $result = $results->get($student->id);
                    $mark = $result ? (float)$result->marks_obtained : '';
                    $status = $result ? $result->attendance_status : 'present';
                    $remarks = $result ? $result->remarks : '';
                    $pct = $result ? $result->percentage : null;
                @endphp
                <tr data-student-id="{{ $student->id }}" class="mark-row" id="row-{{ $student->id }}">
                    <td class="text-sms-muted">{{ $index + 1 }}</td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td>
                        <select class="form-select form-select-sm status-select" data-student="{{ $student->id }}">
                            @foreach(\App\Models\ExamResult::attendanceStatuses() as $val => $label)
                            <option value="{{ $val }}" {{ $status == $val ? 'selected' : '' }}>
                                {{ match($val) { 'present' => 'حاضر', 'absent' => 'غائب', 'excused' => 'معذور', 'cheating' => 'غش', 'incomplete' => 'غير مكتمل', default => $label } }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm mark-input"
                               data-student="{{ $student->id }}"
                               value="{{ $mark }}"
                               min="0" max="{{ $exam->total_marks }}" step="0.5"
                               {{ $status !== 'present' ? 'disabled' : '' }}>
                    </td>
                    <td class="pct-cell fw-bold">{{ $pct !== null ? $pct . '%' : '-' }}</td>
                    <td class="grade-cell">
                        @if($pct !== null)
                            @php
                            $letterGrade = '';
                            $isPassing = false;
                            if ($pct >= 90)      { $letterGrade = 'ممتاز';    $isPassing = true; }
                            elseif ($pct >= 80)  { $letterGrade = 'جيد جداً'; $isPassing = true; }
                            elseif ($pct >= 70)  { $letterGrade = 'جيد';      $isPassing = true; }
                            elseif ($pct >= 60)  { $letterGrade = 'متوسط';    $isPassing = true; }
                            elseif ($pct >= 50)  { $letterGrade = 'مقبول';    $isPassing = true; }
                            else                 { $letterGrade = 'راسب';     $isPassing = false; }
                            @endphp
                            <span class="badge {{ $isPassing ? 'bg-success' : 'bg-danger' }}">{{ $letterGrade }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm remarks-input"
                               data-student="{{ $student->id }}"
                               value="{{ $remarks }}" placeholder="اختياري">
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-sms-muted">لم يتم العثور على طلاب في هذه الشعبة.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>

    <!-- Summary -->
    @if($students->isNotEmpty())
    <x-shared.card class="mt-3" shadow="sm">
        <h6 class="text-sms-muted mb-3"><i class="bi bi-bar-chart-line"></i> ملخص مباشر</h6>
        <div class="row text-center" id="summaryRow">
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">الطلاب</div><div class="fs-5 fw-bold" id="sumTotal">{{ $students->count() }}</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">تم إدخالها</div><div class="fs-5 fw-bold text-success" id="sumEntered">0</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">مفقودة</div><div class="fs-5 fw-bold text-warning" id="sumMissing">0</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">المتوسط</div><div class="fs-5 fw-bold text-primary" id="sumAvg">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">الأعلى</div><div class="fs-5 fw-bold text-success" id="sumHigh">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">الأدنى</div><div class="fs-5 fw-bold text-danger" id="sumLow">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">نسبة النجاح</div><div class="fs-5 fw-bold" id="sumPass">-</div></div></div>
        </div>
    </x-shared.card>
    @endif

    @endif {{-- end if exam --}}

    @if(!$exam && request('exam_id'))
    <div class="text-center py-5">
        <x-shared.empty-state icon="search" title="لم يتم العثور على الاختبار" message="" />
    </div>
    @endif
    @if(!request('exam_id'))
    <div class="text-center py-5">
        <x-shared.empty-state icon="funnel" title="استخدم الفلاتر أعلاه" message="قم بتحميل جدول إدخال الدرجات من خلال تحديد خيارات الفلترة المتاحة." />
    </div>
    @endif
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/modules/assessment.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/modules/assessment/marks-entry.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const marksEntryModule = new SMS.Modules.MarksEntry({
        examId: {{ $exam ? $exam->id : 'null' }},
        totalMarks: {{ $exam ? $exam->total_marks : 'null' }},
        saveUrl: '{{ route("admin.marks-entry.save-mark") }}',
        scales: [
            {percentage_from: 90, percentage_to: 100, letter_grade: 'ممتاز',    is_passing: true},
            {percentage_from: 80, percentage_to: 89.99, letter_grade: 'جيد جداً', is_passing: true},
            {percentage_from: 70, percentage_to: 79.99, letter_grade: 'جيد',      is_passing: true},
            {percentage_from: 60, percentage_to: 69.99, letter_grade: 'متوسط',    is_passing: true},
            {percentage_from: 50, percentage_to: 59.99, letter_grade: 'مقبول',    is_passing: true},
            {percentage_from: 0,  percentage_to: 49.99, letter_grade: 'راسب',     is_passing: false}
        ]
    });
    marksEntryModule.init();
});
</script>
@endpush
