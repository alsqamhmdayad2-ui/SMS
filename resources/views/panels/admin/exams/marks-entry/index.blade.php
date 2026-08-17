@extends('layouts.app')
@section('title', 'إدخال الدرجات')

@section('content')

<x-page-header title="إدخال الدرجات">
    @if($exam ?? false)
    <x-slot name="actions">
        <a href="{{ route('admin.marks-entry.index') }}" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-right"></i> عودة للاختبارات</a>
        <div id="saveIndicator" class="d-inline-flex align-items-center gap-2" style="display:none!important;">
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

    @if(!request('exam_id'))
    <!-- Drill-down Flow Container -->
    <div id="drillDownContainer">
        <!-- Top Filters -->
        <x-shared.card class="mb-4 bg-light" shadow="sm">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.select name="academic_year_id" id="filterYear" label="العام الدراسي">
                        @foreach($academicYears as $y)
                        <option value="{{ $y->id }}"
                            {{ (request('academic_year_id') == $y->id || (!request('academic_year_id') && $currentAcademicYear && $currentAcademicYear->id == $y->id)) ? 'selected' : '' }}>
                            {{ $y->name }}
                        </option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-6">
                    <x-form.select name="semester_id" id="filterSemester" label="الفصل الدراسي">
                        <option value="">الكل</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}"
                            {{ (request('semester_id') == $s->id || (!request('semester_id') && $currentSemester && $currentSemester->id == $s->id)) ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
        </x-shared.card>

        <!-- Breadcrumbs for drill down -->
        <nav aria-label="breadcrumb" id="drillDownBreadcrumb" class="d-none mb-4">
            <ol class="breadcrumb p-3 bg-white rounded shadow-sm border">
                <li class="breadcrumb-item"><a href="#" id="bd-home" class="text-decoration-none fw-bold"><i class="fas fa-home"></i> الصفوف</a></li>
                <li class="breadcrumb-item d-none" id="bd-class-item"><a href="#" id="bd-class" class="text-decoration-none fw-bold"></a></li>
                <li class="breadcrumb-item d-none" id="bd-section-item"><a href="#" id="bd-section" class="text-decoration-none fw-bold"></a></li>
                <li class="breadcrumb-item d-none active" id="bd-subject-item" aria-current="page"><span id="bd-subject" class="text-muted"></span></li>
            </ol>
        </nav>

        <!-- Loading Spinner -->
        <div id="drillDownLoader" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <p class="mt-2 text-muted">جاري تحميل البيانات...</p>
        </div>

        <!-- Step 1: Classes -->
        <div id="step-classes" class="row g-3 fade-in">
            @foreach($classes as $c)
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 hover-lift cursor-pointer class-card border-0 shadow-sm" data-id="{{ $c->id }}" data-name="{{ $c->name }}">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-block mb-3 transition-all icon-container">
                            <i class="fas fa-chalkboard fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $c->name }}</h5>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Step 2: Sections -->
        <div id="step-sections" class="row g-3 d-none fade-in"></div>

        <!-- Step 3: Subjects -->
        <div id="step-subjects" class="row g-3 d-none fade-in"></div>

        <!-- Step 4: Exams -->
        <div id="step-exams" class="row g-3 d-none fade-in"></div>
    </div>
    @endif

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
                <button class="btn btn-sm btn-warning fw-bold shadow-sm" id="btnSaveAll"><i class="fas fa-save"></i> حفظ الكل</button>
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
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/modules/assessment.css') }}" rel="stylesheet">
<style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .hover-lift:hover .icon-container { background-color: var(--bs-primary)!important; color: white!important; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
@if($exam)
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
@else
<script>
document.addEventListener('DOMContentLoaded', function() {
    let state = {
        classId: null,
        className: null,
        sectionId: null,
        sectionName: null,
        subjectId: null,
        subjectName: null,
        examId: null
    };

    const routes = {
        sections: '{{ route("admin.marks-entry.get-sections") }}',
        subjects: '{{ route("admin.marks-entry.get-subjects") }}',
        exams: '{{ route("admin.marks-entry.get-exams") }}',
        marks: '{{ route("admin.marks-entry.index") }}'
    };

    // Elements
    const stepClasses = document.getElementById('step-classes');
    const stepSections = document.getElementById('step-sections');
    const stepSubjects = document.getElementById('step-subjects');
    const stepExams = document.getElementById('step-exams');
    const loader = document.getElementById('drillDownLoader');
    const breadcrumb = document.getElementById('drillDownBreadcrumb');
    
    // Breadcrumb Items
    const bdHome = document.getElementById('bd-home');
    const bdClassItem = document.getElementById('bd-class-item');
    const bdClass = document.getElementById('bd-class');
    const bdSectionItem = document.getElementById('bd-section-item');
    const bdSection = document.getElementById('bd-section');
    const bdSubjectItem = document.getElementById('bd-subject-item');
    const bdSubject = document.getElementById('bd-subject');

    // Filters
    const filterYear = document.getElementById('filterYear');
    const filterSemester = document.getElementById('filterSemester');

    function showLoader(show) {
        if(show) {
            loader.classList.remove('d-none');
        } else {
            loader.classList.add('d-none');
        }
    }

    function hideAllSteps() {
        stepClasses.classList.add('d-none');
        stepSections.classList.add('d-none');
        stepSubjects.classList.add('d-none');
        stepExams.classList.add('d-none');
    }

    // Step 1: Classes
    bdHome.addEventListener('click', (e) => {
        e.preventDefault();
        state = { classId: null, className: null, sectionId: null, sectionName: null, subjectId: null, subjectName: null, examId: null };
        hideAllSteps();
        breadcrumb.classList.add('d-none');
        bdClassItem.classList.add('d-none');
        bdSectionItem.classList.add('d-none');
        bdSubjectItem.classList.add('d-none');
        stepClasses.classList.remove('d-none');
    });

    document.querySelectorAll('.class-card').forEach(card => {
        card.addEventListener('click', function() {
            state.classId = this.dataset.id;
            state.className = this.dataset.name;
            loadSections();
        });
    });

    // Step 2: Sections
    bdClass.addEventListener('click', (e) => {
        e.preventDefault();
        state.sectionId = null; state.sectionName = null; state.subjectId = null; state.subjectName = null;
        hideAllSteps();
        bdSectionItem.classList.add('d-none');
        bdSubjectItem.classList.add('d-none');
        stepSections.classList.remove('d-none');
    });

    function loadSections() {
        hideAllSteps();
        showLoader(true);
        breadcrumb.classList.remove('d-none');
        bdClass.textContent = state.className;
        bdClassItem.classList.remove('d-none');

        fetch(`${routes.sections}?class_id=${state.classId}`)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                stepSections.innerHTML = '';
                if(data.data.length === 0) {
                    stepSections.innerHTML = '<div class="col-12"><div class="alert alert-warning text-center">لا توجد شعب لهذا الصف.</div></div>';
                } else {
                    data.data.forEach(sec => {
                        stepSections.innerHTML += `
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 hover-lift cursor-pointer section-card border-0 shadow-sm" data-id="${sec.id}" data-name="${sec.name}">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-inline-block mb-3 transition-all icon-container">
                                            <i class="fas fa-users fs-3"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0 text-dark">شعبة ${sec.name}</h5>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    document.querySelectorAll('.section-card').forEach(card => {
                        card.addEventListener('click', function() {
                            state.sectionId = this.dataset.id;
                            state.sectionName = this.dataset.name;
                            loadSubjects();
                        });
                    });
                }
                stepSections.classList.remove('d-none');
            })
            .catch(err => {
                showLoader(false);
                alert('حدث خطأ أثناء جلب الشعب.');
            });
    }

    // Step 3: Subjects
    bdSection.addEventListener('click', (e) => {
        e.preventDefault();
        state.subjectId = null; state.subjectName = null;
        hideAllSteps();
        bdSubjectItem.classList.add('d-none');
        stepSubjects.classList.remove('d-none');
    });

    function loadSubjects() {
        hideAllSteps();
        showLoader(true);
        bdSection.textContent = 'شعبة ' + state.sectionName;
        bdSectionItem.classList.remove('d-none');

        const year = filterYear.value;
        const sem = filterSemester.value;

        fetch(`${routes.subjects}?section_id=${state.sectionId}&academic_year_id=${year}&semester_id=${sem}`)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                stepSubjects.innerHTML = '';
                if(data.data.length === 0) {
                    stepSubjects.innerHTML = '<div class="col-12"><div class="alert alert-warning text-center">لا توجد مواد دراسية مرتبطة باختبارات لهذه الشعبة في هذا الفصل.</div></div>';
                } else {
                    data.data.forEach(sub => {
                        stepSubjects.innerHTML += `
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 hover-lift cursor-pointer subject-card border-0 shadow-sm" data-id="${sub.id}" data-name="${sub.name}">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-inline-block mb-3 transition-all icon-container">
                                            <i class="fas fa-book fs-3"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0 text-dark">${sub.name}</h5>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    document.querySelectorAll('.subject-card').forEach(card => {
                        card.addEventListener('click', function() {
                            state.subjectId = this.dataset.id;
                            state.subjectName = this.dataset.name;
                            loadExams();
                        });
                    });
                }
                stepSubjects.classList.remove('d-none');
            })
            .catch(err => {
                showLoader(false);
                alert('حدث خطأ أثناء جلب المواد.');
            });
    }

    // Step 4: Exams
    function loadExams() {
        hideAllSteps();
        showLoader(true);
        bdSubject.textContent = state.subjectName;
        bdSubjectItem.classList.remove('d-none');

        const year = filterYear.value;
        const sem = filterSemester.value;

        fetch(`${routes.exams}?section_id=${state.sectionId}&subject_id=${state.subjectId}&academic_year_id=${year}&semester_id=${sem}`)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                stepExams.innerHTML = '';
                if(data.data.length === 0) {
                    stepExams.innerHTML = '<div class="col-12"><div class="alert alert-warning text-center">لا توجد اختبارات لهذه المادة.</div></div>';
                } else {
                    data.data.forEach(exam => {
                        stepExams.innerHTML += `
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100 hover-lift cursor-pointer exam-card border-0 shadow-sm border-start border-primary border-4" data-id="${exam.id}">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-dark mb-2"><i class="fas fa-file-alt text-primary me-2"></i> ${exam.name}</h5>
                                        <p class="text-muted small mb-0"><i class="fas fa-calculator me-1"></i> الدرجة الكلية: ${exam.total_marks}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    document.querySelectorAll('.exam-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const examId = this.dataset.id;
                            window.location.href = `${routes.marks}?academic_year_id=${year}&semester_id=${sem}&exam_id=${examId}`;
                        });
                    });
                }
                stepExams.classList.remove('d-none');
            })
            .catch(err => {
                showLoader(false);
                alert('حدث خطأ أثناء جلب الاختبارات.');
            });
    }
    
    // Reload subjects if Year or Semester changes mid-flow
    filterYear.addEventListener('change', () => { if(state.sectionId) loadSubjects(); });
    filterSemester.addEventListener('change', () => { if(state.sectionId) loadSubjects(); });
});
</script>
@endif
@endpush
