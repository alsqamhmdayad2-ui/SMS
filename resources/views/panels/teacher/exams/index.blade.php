@extends('layouts.app')
@section('title', 'اختباراتي')

@section('content')

<x-page-header title="اختباراتي">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> اختبار جديد
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي']
]" />

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="إجمالي الاختبارات" :value="$exams->total()" icon="fas fa-file-alt" color="primary" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="مسودة" :value="$exams->getCollection()->where('status.value', 'draft')->count()" icon="fas fa-pencil-alt" color="warning" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="منشورة" :value="$exams->getCollection()->where('status.value', 'published')->count()" icon="fas fa-check-circle" color="success" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="مغلقة" :value="$exams->getCollection()->where('status.value', 'locked')->count()" icon="fas fa-lock" color="info" />
    </div>
</div>

{{-- Drill-down Container --}}
<div id="drillDownContainer">

    {{-- Breadcrumbs for drill down --}}
    <nav aria-label="breadcrumb" id="drillDownBreadcrumb" class="d-none mb-4">
        <ol class="breadcrumb p-3 bg-white rounded shadow-sm border">
            <li class="breadcrumb-item"><a href="#" id="bd-home" class="text-decoration-none fw-bold"><i class="fas fa-home"></i> الشعب</a></li>
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

    <!-- Step 1: Sections -->
    <div id="step-sections" class="row g-3 fade-in">
        @foreach($sections as $sec)
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 hover-lift cursor-pointer section-card border-0 shadow-sm" data-id="{{ $sec->id }}" data-name="{{ $sec->name }}">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-block mb-3 transition-all icon-container">
                        <i class="fas fa-users fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">{{ $sec->schoolClass?->name ?? '' }}</h5>
                    <p class="text-muted small mb-0">شعبة {{ $sec->name }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Step 2: Subjects -->
    <div id="step-subjects" class="row g-3 d-none fade-in"></div>

    <!-- Step 3: Exams list -->
    <div id="step-exams" class="d-none fade-in"></div>
</div>

@endsection

@push('styles')
<style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .hover-lift:hover .icon-container { background-color: var(--bs-primary)!important; color: white!important; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .cursor-pointer { cursor: pointer; }
    .exam-card-item:hover { background: #f8f9fa; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let state = {
        sectionId: null,
        sectionName: null,
        subjectId: null,
        subjectName: null,
    };

    const routes = {
        subjects: '{{ route("teacher.exams.ajax.subjects") }}',
        exams: '{{ route("teacher.exams.ajax.exams") }}',
    };

    const stepSections = document.getElementById('step-sections');
    const stepSubjects = document.getElementById('step-subjects');
    const stepExams = document.getElementById('step-exams');
    const loader = document.getElementById('drillDownLoader');
    const breadcrumb = document.getElementById('drillDownBreadcrumb');
    const bdHome = document.getElementById('bd-home');
    const bdSectionItem = document.getElementById('bd-section-item');
    const bdSection = document.getElementById('bd-section');
    const bdSubjectItem = document.getElementById('bd-subject-item');
    const bdSubject = document.getElementById('bd-subject');

    function showLoader(show) {
        loader.classList.toggle('d-none', !show);
    }

    function hideAllSteps() {
        stepSections.classList.add('d-none');
        stepSubjects.classList.add('d-none');
        stepExams.classList.add('d-none');
    }

    bdHome.addEventListener('click', (e) => {
        e.preventDefault();
        state = { sectionId: null, sectionName: null, subjectId: null, subjectName: null };
        hideAllSteps();
        breadcrumb.classList.add('d-none');
        bdSectionItem.classList.add('d-none');
        bdSubjectItem.classList.add('d-none');
        stepSections.classList.remove('d-none');
    });

    bdSection.addEventListener('click', (e) => {
        e.preventDefault();
        state.subjectId = null; state.subjectName = null;
        hideAllSteps();
        bdSubjectItem.classList.add('d-none');
        stepSubjects.classList.remove('d-none');
    });

    // Step 1 click
    document.querySelectorAll('.section-card').forEach(card => {
        card.addEventListener('click', function() {
            state.sectionId = this.dataset.id;
            state.sectionName = this.dataset.name;
            loadSubjects();
        });
    });

    function loadSubjects() {
        hideAllSteps();
        showLoader(true);
        breadcrumb.classList.remove('d-none');
        bdSection.textContent = 'شعبة ' + state.sectionName;
        bdSectionItem.classList.remove('d-none');

        fetch(`${routes.subjects}?section_id=${state.sectionId}`)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                stepSubjects.innerHTML = '';
                const subjects = data.data || [];
                if(subjects.length === 0) {
                    stepSubjects.innerHTML = '<div class="col-12"><div class="alert alert-warning text-center">لا توجد مواد مرتبطة باختبارات في هذه الشعبة.</div></div>';
                } else {
                    const colors = ['info', 'success', 'warning', 'danger', 'primary', 'secondary'];
                    subjects.forEach((sub, i) => {
                        const color = colors[i % colors.length];
                        stepSubjects.innerHTML += `
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 hover-lift cursor-pointer subject-card border-0 shadow-sm" data-id="${sub.id}" data-name="${sub.name}">
                                    <div class="card-body text-center p-4">
                                        <div class="rounded-circle bg-${color} bg-opacity-10 text-${color} p-3 d-inline-block mb-3 transition-all icon-container">
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
            .catch(() => { showLoader(false); alert('حدث خطأ أثناء جلب المواد.'); });
    }

    function loadExams() {
        hideAllSteps();
        showLoader(true);
        bdSubject.textContent = state.subjectName;
        bdSubjectItem.classList.remove('d-none');

        fetch(`${routes.exams}?section_id=${state.sectionId}&subject_id=${state.subjectId}`)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                stepExams.innerHTML = '';
                const exams = data.data || [];
                if(exams.length === 0) {
                    stepExams.innerHTML = '<div class="alert alert-warning text-center">لا توجد اختبارات لهذه المادة.</div>';
                } else {
                    let html = '<div class="card shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th class="ps-3">الاختبار</th><th>التاريخ</th><th>الدرجة الكلية</th><th>الحالة</th><th class="text-center">الإجراءات</th></tr></thead><tbody>';
                    exams.forEach(exam => {
                        const statusBadge = {
                            'draft': '<span class="badge bg-warning text-dark">مسودة</span>',
                            'published': '<span class="badge bg-success">منشور</span>',
                            'locked': '<span class="badge bg-secondary">مغلق</span>',
                        }[exam.status] || `<span class="badge bg-light text-dark">${exam.status}</span>`;

                        let actions = `<a href="${exam.urls.show}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i> عرض</a>`;

                        if(exam.status === 'draft') {
                            actions += `
                                <a href="${exam.urls.questions}" class="btn btn-sm btn-outline-info"><i class="fas fa-list-ol me-1"></i> الأسئلة</a>
                                <a href="${exam.urls.edit}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                <button onclick="confirmPublish('${exam.urls.publish}')" class="btn btn-sm btn-outline-success" title="نشر"><i class="fas fa-bullhorn"></i></button>
                                <button onclick="confirmDelete('${exam.urls.destroy}')" class="btn btn-sm btn-outline-danger" title="حذف"><i class="fas fa-trash"></i></button>
                            `;
                        }

                        html += `<tr><td class="ps-3"><div class="fw-semibold">${exam.name}</div><small class="text-muted">${exam.type}</small></td><td class="text-muted small">${exam.date}</td><td><span class="badge bg-primary">${exam.total_marks}</span></td><td>${statusBadge}</td><td class="text-center"><div class="d-flex justify-content-center gap-1 flex-wrap">${actions}</div></td></tr>`;
                    });
                    html += '</tbody></table></div></div></div>';
                    stepExams.innerHTML = html;
                }
                stepExams.classList.remove('d-none');
            })
            .catch(() => { showLoader(false); alert('حدث خطأ أثناء جلب الاختبارات.'); });
    }

    function confirmPublish(url) {
        if(!confirm('هل أنت متأكد من نشر الاختبار؟ لن تتمكن من تعديل الأسئلة بعد النشر.')) return;
        const form = document.createElement('form');
        form.method = 'POST'; form.action = url;
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
        document.body.appendChild(form);
        form.submit();
    }

    function confirmDelete(url) {
        if(!confirm('هل أنت متأكد من حذف هذا الاختبار؟')) return;
        const form = document.createElement('form');
        form.method = 'POST'; form.action = url;
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endpush
