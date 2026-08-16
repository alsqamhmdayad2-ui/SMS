@extends('layouts.app')
@section('title', 'إصدار الشهادات')

@section('content')

<x-page-header title="إصدار الشهادات والنتائج" />

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'النتائج والشهادات'],
    ['name' => 'إصدار الشهادات']
]" />

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4 mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 text-sms-main"><i class="bi bi-printer me-2"></i> خيارات طباعة الشهادات</h5>
                
                <form action="{{ route('admin.report-cards.generate') }}" method="POST" target="_blank">
                    @csrf
                    
                    <div class="mb-3">
                        <x-form.select name="certificate_type" label="نوع الشهادة" required="true">
                            <option value="annual">شهادة نهاية العام (تشمل الفصلين)</option>
                            <option value="semester">شهادة فصلية (لفصل واحد فقط)</option>
                        </x-form.select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <x-form.select name="academic_year_id" label="العام الدراسي" required="true">
                                @foreach($academicYears as $y)
                                <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sms-main">الفصل الدراسي (في حال الشهادة الفصلية) <span class="text-sms-danger" id="semester_req_star">*</span></label>
                            <select name="semester_id" id="semester_id" class="form-select" required>
                                <option value="">اختر الفصل...</option>
                                @foreach($semesters as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sms-main">الشعبة <span class="text-sms-danger">*</span></label>
                        <select name="section_id" class="form-select" required>
                            <option value="">اختر الشعبة...</option>
                            @php $sections = \App\Models\Section::with('schoolClass')->get(); @endphp
                            @foreach($sections as $sec)
                            <option value="{{ $sec->id }}">{{ $sec->schoolClass->name ?? '' }} - {{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-bold text-white shadow-sm">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i> إصدار وطباعة الشهادات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.querySelector('select[name="certificate_type"]');
        const semesterSelect = document.getElementById('semester_id');
        const semesterStar = document.getElementById('semester_req_star');

        function toggleSemester() {
            if (typeSelect.value === 'annual') {
                semesterSelect.removeAttribute('required');
                semesterSelect.disabled = true;
                semesterSelect.value = '';
                semesterStar.style.display = 'none';
            } else {
                semesterSelect.setAttribute('required', 'required');
                semesterSelect.disabled = false;
                semesterStar.style.display = 'inline';
            }
        }

        typeSelect.addEventListener('change', toggleSemester);
        toggleSemester(); // run on load
    });
</script>
@endpush

@endsection
