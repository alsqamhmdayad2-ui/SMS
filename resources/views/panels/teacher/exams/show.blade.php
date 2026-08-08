@extends('layouts.app')
@section('title', 'إدخال درجات — ' . $exam->title)

@section('content')

<x-page-header title="إدخال الدرجات">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
        @if($exam->status->value === 'draft')
        <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-edit me-1"></i> تعديل الاختبار
        </a>
        @endif
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي', 'url' => route('teacher.exams.index')],
    ['name' => $exam->title]
]" />

{{-- Exam Info Banner --}}
<div class="card shadow-sm mb-4 border-start border-primary border-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="fw-bold fs-5">{{ $exam->title }}</div>
                <div class="text-muted small">{{ $exam->subject?->name }} — {{ $exam->section?->schoolClass?->name }} / {{ $exam->section?->name }}</div>
            </div>
            <div class="col-md-2 text-center">
                <div class="text-muted small">النوع</div>
                <strong>{{ match($exam->type) { 'quiz' => 'اختبار قصير', 'midterm' => 'نصف فصلي', 'final' => 'نهائي', 'assignment' => 'واجب', default => $exam->type } }}</strong>
            </div>
            <div class="col-md-2 text-center">
                <div class="text-muted small">الدرجة الكلية</div>
                <strong class="text-primary fs-5">{{ $exam->total_marks ?: '—' }}</strong>
            </div>
            <div class="col-md-2 text-center">
                <div class="text-muted small">تاريخ الاختبار</div>
                <strong>{{ $exam->exam_date?->format('Y-m-d') ?? '—' }}</strong>
            </div>
            <div class="col-md-2 text-center">
                @php $st = $exam->status->value ?? 'draft'; @endphp
                <span class="badge fs-6 rounded-pill {{ match($st) { 'draft' => 'bg-warning text-dark', 'published' => 'bg-success', 'locked' => 'bg-secondary', default => 'bg-light text-dark' } }}">
                    {{ match($st) { 'draft' => 'مسودة', 'published' => 'منشور', 'locked' => 'مغلق', default => $st } }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Marks Notice if no questions --}}
@if($exam->total_marks == 0)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    لم يتم تحديد الدرجة الكلية لهذا الاختبار. يرجى التواصل مع الإدارة لتعيين الدرجة الكلية.
</div>
@endif

{{-- Save All Button --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>قائمة الطلاب ({{ $students->count() }} طالب)</h6>
    <div class="d-flex gap-2">
        <div id="saveStatus" class="text-muted small align-self-center"></div>
        <button id="saveAllBtn" class="btn btn-success btn-sm" onclick="saveAllMarks()">
            <i class="fas fa-save me-1"></i> حفظ الكل
        </button>
    </div>
</div>

{{-- Students Marks Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>اسم الطالب</th>
                        <th>حالة الحضور</th>
                        <th>الدرجة @if($exam->total_marks) <span class="text-muted fw-normal">/ {{ $exam->total_marks }}</span> @endif</th>
                        <th>النسبة</th>
                        <th>التقدير</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    @php $result = $results[$student->id] ?? null; @endphp
                    <tr data-student-id="{{ $student->id }}">
                        <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center"
                                     style="width:34px;height:34px;font-size:13px;flex-shrink:0">
                                    {{ mb_substr($student->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $student->first_name }} {{ $student->family_name }}</div>
                                    <small class="text-muted">{{ $student->student_number }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <select class="form-select form-select-sm attendance-select" style="min-width: 120px;"
                                    data-student="{{ $student->id }}"
                                    onchange="autoSave({{ $student->id }})">
                                <option value="present" {{ ($result?->attendance_status ?? 'present') === 'present' ? 'selected' : '' }}>حاضر</option>
                                <option value="absent"  {{ ($result?->attendance_status) === 'absent' ? 'selected' : '' }}>غائب</option>
                                <option value="excused" {{ ($result?->attendance_status) === 'excused' ? 'selected' : '' }}>معذور</option>
                                <option value="cheating" {{ ($result?->attendance_status) === 'cheating' ? 'selected' : '' }}>غش</option>
                                <option value="incomplete" {{ ($result?->attendance_status) === 'incomplete' ? 'selected' : '' }}>لم يكمل</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm mark-input"
                                   style="max-width:90px;"
                                   data-student="{{ $student->id }}"
                                   value="{{ $result?->marks_obtained }}"
                                   min="0" max="{{ $exam->total_marks ?: 100 }}"
                                   step="0.5"
                                   onblur="autoSave({{ $student->id }})">
                        </td>
                        <td>
                            <span class="percentage-badge badge bg-light text-dark border" data-student="{{ $student->id }}">
                                {{ $result?->percentage ? $result->percentage . '%' : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-badge fw-bold" data-student="{{ $student->id }}">
                                {{ $result?->letter_grade ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm remarks-input"
                                   style="max-width:150px;"
                                   data-student="{{ $student->id }}"
                                   value="{{ $result?->remarks }}"
                                   placeholder="ملاحظة..."
                                   onblur="autoSave({{ $student->id }})">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                            لا يوجد طلاب في هذه الشعبة.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
const EXAM_ID  = {{ $exam->id }};
const SAVE_URL = "{{ route('teacher.exams.marks.save') }}";
const SAVE_ALL_URL = "{{ route('teacher.exams.marks.save-all') }}";
const CSRF     = "{{ csrf_token() }}";

function getStudentData(studentId) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    return {
        student_id:        studentId,
        marks_obtained:    row.querySelector('.mark-input').value || null,
        attendance_status: row.querySelector('.attendance-select').value,
        remarks:           row.querySelector('.remarks-input').value || null,
    };
}

function autoSave(studentId) {
    const data = getStudentData(studentId);
    setStatus('جاري الحفظ...');

    fetch(SAVE_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ exam_id: EXAM_ID, ...data })
    })
    .then(r => r.json())
    .then(res => {
        if (res.data) {
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            if (res.data.percentage !== undefined) {
                row.querySelector('.percentage-badge').textContent = res.data.percentage ? res.data.percentage + '%' : '—';
            }
            if (res.data.letter_grade) {
                row.querySelector('.grade-badge').textContent = res.data.letter_grade;
            }
        }
        setStatus('✓ تم الحفظ');
    })
    .catch(() => setStatus('✗ فشل الحفظ'));
}

function saveAllMarks() {
    const results = [];
    document.querySelectorAll('tr[data-student-id]').forEach(row => {
        const id = row.dataset.studentId;
        results.push(getStudentData(parseInt(id)));
    });

    setStatus('جاري حفظ الكل...');
    fetch(SAVE_ALL_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ exam_id: EXAM_ID, results })
    })
    .then(r => r.json())
    .then(res => setStatus('✓ ' + (res.message || 'تم حفظ الكل')))
    .catch(() => setStatus('✗ فشل الحفظ الجماعي'));
}

function setStatus(msg) {
    document.getElementById('saveStatus').textContent = msg;
    setTimeout(() => document.getElementById('saveStatus').textContent = '', 3000);
}
</script>
@endpush

@endsection
