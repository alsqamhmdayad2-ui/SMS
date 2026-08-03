@extends('layouts.app')
@section('title', 'بناء الجدول الدراسي')

@section('content')

<x-page-header title="بناء الجدول الدراسي">
    <x-slot:actions>
        <a href="{{ route('admin.timetables.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع للجدول</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الجداول الدراسية', 'url' => route('admin.timetables.index')],
    ['name' => 'بناء الجدول']
]" />

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.timetables.build') }}" id="sectionForm">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label for="class_select" class="form-label small fw-bold">اختر الصف <span class="text-danger">*</span></label>
                    <select class="form-select" id="class_select" required>
                        <option value="">-- يرجى اختيار الصف --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ ($selectedSection && $selectedSection->class_id == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->grade->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="section_id" class="form-label small fw-bold">اختر الشعبة <span class="text-danger">*</span></label>
                    <select class="form-select" name="section_id" id="section_id" required {{ $selectedSection ? '' : 'disabled' }}>
                        <option value="">-- يرجى اختيار الشعبة --</option>
                        <!-- Options populated via JS -->
                    </select>
                </div>
                @if($selectedSection)
                <div class="col-md-7 text-end">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        <i class="fas fa-info-circle me-1"></i> يتم بناء الجدول على أساس: {{ count($periods) }} حصص يومياً
                    </span>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedSection)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-calendar-alt me-2"></i>جدول شعبة: {{ $selectedSection->name }} - {{ $selectedSection->schoolClass->name }}</h6>
        </div>
        <div class="card-body p-0">
            <form method="POST" action="{{ route('admin.timetables.save') }}">
                @csrf
                <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="12%" class="text-muted small">اليوم / الحصة</th>
                                @foreach($periods as $period)
                                    <th width="14%">الحصة {{ $period }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $day)
                                <tr>
                                    <td class="fw-bold bg-light">{{ $day }}</td>
                                    @foreach($periods as $period)
                                        @php
                                            $currentSubjectId = $weeklySchedule[$day][$period] ?? null;
                                        @endphp
                                        <td class="p-2">
                                            <select class="form-select form-select-sm subject-select" 
                                                    name="schedule[{{ $day }}][{{ $period }}]"
                                                    data-day="{{ $day }}" data-period="{{ $period }}">
                                                <option value="">- فراغ -</option>
                                                @foreach($sectionSubjects as $subj)
                                                    <option value="{{ $subj->subject_id }}" 
                                                            data-teacher="{{ $subj->first_name }} {{ $subj->family_name }}"
                                                            data-teacher-id="{{ $subj->teacher_id }}"
                                                            {{ $currentSubjectId == $subj->subject_id ? 'selected' : '' }}>
                                                        {{ $subj->subject_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="teacher-name text-muted small mt-1 fw-bold" style="min-height: 1.2em; font-size: 0.75rem;">
                                                <!-- Auto-populated by JS -->
                                            </div>
                                            <div class="conflict-msg text-danger small mt-1" style="display:none; font-size: 0.7rem; font-weight:bold;">
                                                <i class="fas fa-exclamation-triangle"></i> <span></span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-light p-3 d-flex justify-content-end gap-2 align-items-center">
                    <div id="global-conflict-warning" class="text-danger me-auto fw-bold" style="display:none;">
                        <i class="fas fa-exclamation-circle"></i> يرجى حل التعارضات قبل الحفظ!
                    </div>
                    <a href="{{ route('admin.timetables.index') }}" class="btn btn-secondary px-4">إلغاء</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> حفظ الجدول واعتماده</button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px;">
            <i class="fas fa-table fa-2x text-secondary"></i>
        </div>
        <p class="mb-0">يرجى تحديد الشعبة من القائمة أعلاه لبناء جدولها الأسبوعي.</p>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classesData = @json($classes->map(function($c) {
        return ['id' => $c->id, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()];
    })->values());
    
    const selectedSectionId = {{ $selectedSection ? $selectedSection->id : 'null' }};
    const classSelect = document.getElementById('class_select');
    const sectionSelect = document.getElementById('section_id');
    const form = document.getElementById('sectionForm');

    function filterSections() {
        const classId = classSelect.value;
        sectionSelect.innerHTML = '<option value="">-- يرجى اختيار الشعبة --</option>';
        
        if (!classId) {
            sectionSelect.disabled = true;
            return;
        }

        sectionSelect.disabled = false;
        const selectedClass = classesData.find(c => c.id == classId);
        
        if (selectedClass && selectedClass.sections) {
            selectedClass.sections.forEach(section => {
                const isSelected = selectedSectionId == section.id ? 'selected' : '';
                sectionSelect.innerHTML += `<option value="${section.id}" ${isSelected}>${section.name}</option>`;
            });
        }
    }

    classSelect.addEventListener('change', filterSections);
    
    sectionSelect.addEventListener('change', function() {
        if (this.value) {
            form.submit();
        }
    });

    if (classSelect.value) {
        filterSections();
    }

    const selects = document.querySelectorAll('.subject-select');
    const saveBtn = document.getElementById('saveBtn');
    const globalWarning = document.getElementById('global-conflict-warning');
    const sectionId = document.querySelector('input[name="section_id"]').value;
    
    // Store conflict status
    let conflicts = new Set();

    function checkGlobalConflicts() {
        if (conflicts.size > 0) {
            saveBtn.disabled = true;
            globalWarning.style.display = 'block';
        } else {
            saveBtn.disabled = false;
            globalWarning.style.display = 'none';
        }
    }
    
    async function updateTeacherAndCheckConflict(select) {
        const option = select.options[select.selectedIndex];
        const teacherContainer = select.nextElementSibling;
        const conflictContainer = teacherContainer.nextElementSibling;
        const conflictSpan = conflictContainer.querySelector('span');
        const day = select.dataset.day;
        const period = select.dataset.period;
        const cellId = `${day}-${period}`;
        
        let teacherId = null;

        if (option.value !== '') {
            const teacherName = option.getAttribute('data-teacher');
            // Assuming data-teacher-id is added or we just rely on name check.
            // Wait, we need teacher_id for the backend check. Let's get it from the selected subject.
            // Actually, we didn't add data-teacher-id to the option. I should add it!
            // But we can check via subject if teacher exists.
            
            if (teacherName && teacherName.trim() !== 'null null') {
                teacherContainer.innerHTML = '<i class="fas fa-user-tie me-1"></i>' + teacherName;
                teacherContainer.classList.remove('text-danger');
                teacherContainer.classList.add('text-muted');
                teacherId = option.getAttribute('data-teacher-id');
            } else {
                teacherContainer.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>لا يوجد معلم';
                teacherContainer.classList.remove('text-muted');
                teacherContainer.classList.add('text-danger');
            }
        } else {
            teacherContainer.innerHTML = '';
            conflictContainer.style.display = 'none';
            conflicts.delete(cellId);
            select.classList.remove('is-invalid');
            checkGlobalConflicts();
            return;
        }

        // Run AJAX conflict check if teacher exists
        if (teacherId) {
            try {
                const response = await fetch('{{ route("admin.timetables.check_conflict") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        teacher_id: teacherId,
                        day_of_week: day,
                        period_number: period,
                        section_id: sectionId
                    })
                });
                const data = await response.json();
                
                if (data.hasConflict) {
                    conflictSpan.innerText = data.message;
                    conflictContainer.style.display = 'block';
                    select.classList.add('is-invalid');
                    conflicts.add(cellId);
                } else {
                    conflictContainer.style.display = 'none';
                    select.classList.remove('is-invalid');
                    conflicts.delete(cellId);
                }
                checkGlobalConflicts();
            } catch (e) {
                console.error("Conflict check failed", e);
            }
        } else {
            conflictContainer.style.display = 'none';
            select.classList.remove('is-invalid');
            conflicts.delete(cellId);
            checkGlobalConflicts();
        }
    }
    
    selects.forEach(select => {
        // Run on change
        select.addEventListener('change', function() {
            updateTeacherAndCheckConflict(this);
        });
        
        // Initial setup for teacher name without triggering fetch loop initially
        const option = select.options[select.selectedIndex];
        const teacherContainer = select.nextElementSibling;
        if (option.value !== '') {
            const teacherName = option.getAttribute('data-teacher');
            if (teacherName && teacherName.trim() !== 'null null') {
                teacherContainer.innerHTML = '<i class="fas fa-user-tie me-1"></i>' + teacherName;
            }
        }
    });
});
</script>
@endpush
