@extends('layouts.app')
@section('title', 'التقارير الأكاديمية')

@section('content')
<x-page-header title="التقارير الأكاديمية" />
<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقارير الأكاديمية'],
]" />

<div class="row g-4">

    {{-- Student Report Card --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-primary text-white rounded-top">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> كشف درجات طالب</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">تقرير رسمي لطالب واحد يشمل جميع المواد والـ GPA.</p>
                <form action="{{ route('admin.reports.generate', 'student') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">الفصل الدراسي</label>
                        <select name="semester_id" class="form-select form-select-sm">
                            <option value="">كل الفصول</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Grade & Section filter for narrowing students --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold">الصف/الشعبة (اختياري)</label>
                        <select id="srSection" class="form-select form-select-sm">
                            <option value="">كل الشعب</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}">
                                    {{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">بحث عن طالب</label>
                        <input type="text" id="srSearch" class="form-control form-control-sm" placeholder="اكتب اسم الطالب...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الطالب</label>
                        <select name="student_id" id="srStudentSelect" class="form-select form-select-sm" required size="4">
                            @foreach($students as $student)
                                <option value="{{ $student->id }}"
                                    data-name="{{ $student->name }}"
                                    data-section="{{ $student->section_id }}">
                                    {{ $student->name }}
                                    @if($student->section)
                                        ({{ ($student->section->schoolClass->name ?? '') . ' - ' . $student->section->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Section Report --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-success text-white rounded-top">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> نتائج الشعبة</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">نتائج شاملة لشعبة كاملة مع ترتيب الطلاب والمتوسطات.</p>
                <form action="{{ route('admin.reports.generate', 'section') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">الفصل الدراسي</label>
                        <select name="semester_id" class="form-select form-select-sm">
                            <option value="">كل الفصول</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الشعبة</label>
                        <select name="section_id" class="form-select form-select-sm" required>
                            <option value="">اختر الشعبة...</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-success btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Subject Report --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-info text-white rounded-top">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i> تقرير مادة</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">تفاصيل مكونات التقييم لمادة معينة في شعبة محددة.</p>
                <form action="{{ route('admin.reports.generate', 'subject') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">الشعبة</label>
                        <select name="section_id" class="form-select form-select-sm" required>
                            <option value="">اختر الشعبة...</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المادة</label>
                        <select name="subject_id" class="form-select form-select-sm" required>
                            <option value="">اختر المادة...</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-info btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Annual Report --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header text-white rounded-top" style="background: linear-gradient(135deg,#7c3aed,#4f46e5);">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> التقرير السنوي</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">تقرير شامل للعام الدراسي يجمع نتائج جميع الفصول لطالب أو شعبة.</p>
                <form action="{{ route('admin.reports.generate', 'annual') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">نوع التقرير</label>
                        <select id="annualType" class="form-select form-select-sm">
                            <option value="student">لطالب معين</option>
                            <option value="section">لشعبة كاملة</option>
                        </select>
                    </div>
                    <div id="annualStudentField" class="mb-3">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">الصف/الشعبة (اختياري)</label>
                            <select id="annStudentSection" class="form-select form-select-sm">
                                <option value="">كل الشعب</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}">
                                        {{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">بحث عن طالب</label>
                            <input type="text" id="annStudentSearch" class="form-control form-control-sm" placeholder="اكتب اسم الطالب...">
                        </div>
                        <label class="form-label fw-semibold">الطالب</label>
                        <select name="student_id" id="annStudentSelect" class="form-select form-select-sm" size="4">
                            @foreach($students as $student)
                                <option value="{{ $student->id }}"
                                    data-name="{{ $student->name }}"
                                    data-section="{{ $student->section_id }}">
                                    {{ $student->name }}
                                    @if($student->section)
                                        ({{ ($student->section->schoolClass->name ?? '') . ' - ' . $student->section->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="annualSectionField" class="mb-3 d-none">
                        <label class="form-label fw-semibold">الشعبة</label>
                        <select name="section_id" class="form-select form-select-sm">
                            <option value="">اختر الشعبة...</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Teacher Report --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-warning text-dark rounded-top">
                <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i> تقرير معلم</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">ملخص أداء الطلاب لجميع المواد والشعب المرتبطة بمعلم.</p>
                <form action="{{ route('admin.reports.generate', 'teacher') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المعلم</label>
                        <select name="teacher_id" class="form-select form-select-sm" required>
                            <option value="">اختر المعلم...</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-warning btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-secondary text-white rounded-top">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> الإحصاءات الأكاديمية</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">إحصاءات عامة، نسب النجاح، توزيع الدرجات وقوائم الشرف.</p>
                <form action="{{ route('admin.reports.generate', 'statistics') }}" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">العام الدراسي</label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الشعبة (اختياري)</label>
                        <select name="section_id" class="form-select form-select-sm">
                            <option value="">كل الشعب</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="view" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye me-1"></i> عرض</button>
                        <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Annual Report toggle
    const annualType = document.getElementById('annualType');
    const studentField = document.getElementById('annualStudentField');
    const sectionField = document.getElementById('annualSectionField');
    if (annualType) {
        annualType.addEventListener('change', function () {
            if (this.value === 'student') {
                studentField.classList.remove('d-none');
                sectionField.classList.add('d-none');
            } else {
                studentField.classList.add('d-none');
                sectionField.classList.remove('d-none');
            }
        });
    }

    // ---- Student Report searchable list ----
    const srSearch   = document.getElementById('srSearch');
    const srSection  = document.getElementById('srSection');
    const srSelect   = document.getElementById('srStudentSelect');

    function filterStudents() {
        if (!srSelect) return;
        const query      = (srSearch  ? srSearch.value.trim().toLowerCase()  : '');
        const sectionId  = (srSection ? srSection.value : '');

        Array.from(srSelect.options).forEach(opt => {
            const name    = (opt.dataset.name    || '').toLowerCase();
            const secId   = (opt.dataset.section || '');
            const matchQ  = !query     || name.includes(query);
            const matchS  = !sectionId || secId === sectionId;
            opt.hidden = !(matchQ && matchS);
        });

        // auto-select first visible option
        const firstVisible = Array.from(srSelect.options).find(o => !o.hidden);
        if (firstVisible) firstVisible.selected = true;
    }

    if (srSearch)  srSearch.addEventListener('input',  filterStudents);
    if (srSection) srSection.addEventListener('change', filterStudents);

    // ---- Annual student search ----
    const annSearch  = document.getElementById('annStudentSearch');
    const annSection = document.getElementById('annStudentSection');
    const annSelect  = document.getElementById('annStudentSelect');

    function filterAnnualStudents() {
        if (!annSelect) return;
        const query     = (annSearch  ? annSearch.value.trim().toLowerCase() : '');
        const sectionId = (annSection ? annSection.value : '');

        Array.from(annSelect.options).forEach(opt => {
            const name   = (opt.dataset.name    || '').toLowerCase();
            const secId  = (opt.dataset.section || '');
            const matchQ = !query     || name.includes(query);
            const matchS = !sectionId || secId === sectionId;
            opt.hidden   = !(matchQ && matchS);
        });

        const firstVisible = Array.from(annSelect.options).find(o => !o.hidden);
        if (firstVisible) firstVisible.selected = true;
    }

    if (annSearch)  annSearch.addEventListener('input',  filterAnnualStudents);
    if (annSection) annSection.addEventListener('change', filterAnnualStudents);
});
</script>
@endpush
