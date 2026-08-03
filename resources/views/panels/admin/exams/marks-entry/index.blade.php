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
                <div class="col-md-2">
                    <x-form.select name="academic_year_id" id="filterYear" label="Academic Year" required="true">
                        <option value="">Select...</option>
                        @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-2">
                    <x-form.select name="semester_id" id="filterSemester" label="Semester">
                        <option value="">All</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-2">
                    <x-form.select name="grade_id" id="filterGrade" label="Grade" required="true">
                        <option value="">Select...</option>
                        @foreach($grades as $g)
                        <option value="{{ $g->id }}" {{ request('grade_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-2">
                    <x-form.select name="section_id" id="filterSection" label="Section" required="true" data-url="{{ route('admin.marks-entry.get-sections') }}">
                        <option value="">Select...</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-2">
                    <x-form.select name="subject_id" id="filterSubject" label="Subject" required="true">
                        <option value="">Select...</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-2">
                    <x-form.select name="exam_id" id="filterExam" label="Exam" required="true">
                        <option value="">Select...</option>
                        @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ request('exam_id') == $e->id ? 'selected' : '' }}>{{ $e->title }} ({{ ucfirst($e->type) }})</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Load Students</button>
            </div>
        </form>
    </x-shared.card>

    @if($exam)
    <!-- Exam Info Bar -->
    <x-shared.card class="mb-3 border-start border-sms-primary border-4" shadow="sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $exam->title }}</strong> &mdash; {{ $exam->subject->name ?? '' }} |
                Total Marks: <x-shared.badge type="primary" class="fs-6">{{ $exam->total_marks }}</x-shared.badge>
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
                <th>Student Name</th>
                <th style="width:130px">Status</th>
                <th style="width:100px">Mark / {{ $exam->total_marks }}</th>
                <th style="width:80px">%</th>
                <th style="width:80px">Grade</th>
                <th style="width:180px">Remarks</th>
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
                            <option value="{{ $val }}" {{ $status == $val ? 'selected' : '' }}>{{ $label }}</option>
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
                            @php $gr = $gradeScales->first(fn($s) => $pct >= (float)$s->percentage_from && $pct <= (float)$s->percentage_to); @endphp
                            @if($gr)
                            <span class="badge {{ $gr->is_passing ? 'bg-success' : 'bg-danger' }}">{{ $gr->letter_grade }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm remarks-input"
                               data-student="{{ $student->id }}"
                               value="{{ $remarks }}" placeholder="Optional">
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-sms-muted">No students found in this section.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>

    <!-- Summary -->
    @if($students->isNotEmpty())
    <x-shared.card class="mt-3" shadow="sm">
        <h6 class="text-sms-muted mb-3"><i class="bi bi-bar-chart-line"></i> Live Summary</h6>
        <div class="row text-center" id="summaryRow">
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Students</div><div class="fs-5 fw-bold" id="sumTotal">{{ $students->count() }}</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Entered</div><div class="fs-5 fw-bold text-success" id="sumEntered">0</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Missing</div><div class="fs-5 fw-bold text-warning" id="sumMissing">0</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Average</div><div class="fs-5 fw-bold text-primary" id="sumAvg">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Highest</div><div class="fs-5 fw-bold text-success" id="sumHigh">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Lowest</div><div class="fs-5 fw-bold text-danger" id="sumLow">-</div></div></div>
            <div class="col"><div class="border rounded p-2"><div class="small text-sms-muted">Pass Rate</div><div class="fs-5 fw-bold" id="sumPass">-</div></div></div>
        </div>
    </x-shared.card>
    @endif

    @endif {{-- end if exam --}}

    @if(!$exam && request('exam_id'))
    <div class="text-center py-5">
        <x-shared.empty-state icon="search" title="Exam not found" message="" />
    </div>
    @endif
    @if(!request('exam_id'))
    <div class="text-center py-5">
        <x-shared.empty-state icon="funnel" title="Select filters above" message="Load the marks entry grid by selecting filters." />
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
    @if($exam)
    const marksEntryModule = new SMS.Modules.MarksEntry({
        examId: {{ $exam->id }},
        totalMarks: {{ $exam->total_marks }},
        saveUrl: '{{ route("admin.marks-entry.save-mark") }}',
        scales: @json($gradeScales)
    });
    marksEntryModule.init();
    @endif
});
</script>
@endpush
