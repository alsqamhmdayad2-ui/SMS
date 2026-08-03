@extends('layouts.app')
@section('title', 'الجداول الدراسية')

@section('content')

<x-page-header title="الجداول الدراسية الأسبوعية">
    <x-slot:actions>
        <a href="{{ route('admin.timetables.build') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i> تعديل يدوي
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الجداول الدراسية']
]" />



<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.timetables.index') }}" id="sectionForm">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label for="class_select" class="form-label small fw-bold">اختر الصف</label>
                    <select class="form-select" id="class_select">
                        <option value="">-- يرجى اختيار الصف --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ ($selectedSection && $selectedSection->class_id == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->grade->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="section_id" class="form-label small fw-bold">اختر الشعبة لعرض الجدول</label>
                    <select class="form-select" name="section_id" id="section_id" {{ $selectedSection ? '' : 'disabled' }}>
                        <option value="">-- يرجى اختيار الشعبة --</option>
                        <!-- Options populated via JS -->
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedSection)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-calendar-alt me-2"></i>جدول شعبة: {{ $selectedSection->name }} - {{ $selectedSection->schoolClass->name }}</h6>
            <div class="d-flex gap-2 flex-wrap">
                {{-- Auto Generate Button --}}
                <form action="{{ route('admin.timetables.generate') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3"
                        onclick="return confirm('سيتم إنشاء جدول جديد من الصفر لهذه الشعبة. هل تريد المتابعة؟')">
                        <i class="fas fa-magic me-1"></i> إنشاء تلقائي
                    </button>
                </form>
                {{-- Regenerate Button (only if timetable exists) --}}
                @if(collect($weeklySchedule)->flatten()->filter()->isNotEmpty())
                <form action="{{ route('admin.timetables.regenerate') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3"
                        onclick="return confirm('سيتم إعادة توليد الجدول وحذف الجدول الحالي. هل تريد المتابعة؟')">
                        <i class="fas fa-sync-alt me-1"></i> إعادة توليد
                    </button>
                </form>
                @endif
                {{-- Manual Edit Button --}}
                <a href="{{ route('admin.timetables.build', ['section_id' => $selectedSection->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-edit me-1"></i> تعديل يدوي
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @php $hasData = collect($weeklySchedule)->flatten()->filter()->isNotEmpty(); @endphp
            
            @if(!$hasData)
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                    <p class="mb-2 fw-bold">لم يتم بناء جدول لهذه الشعبة بعد.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                        <form action="{{ route('admin.timetables.generate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-magic me-2"></i> إنشاء تلقائي
                            </button>
                        </form>
                        <a href="{{ route('admin.timetables.build', ['section_id' => $selectedSection->id]) }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-pencil-alt me-2"></i> بناء يدوي
                        </a>
                    </div>
                </div>
            @else
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
                                            $entry = $weeklySchedule[$day][$period] ?? null;
                                        @endphp
                                        <td>
                                            @if($entry)
                                                <div class="p-2 border rounded bg-white shadow-sm" style="border-left: 3px solid #0d6efd !important;">
                                                    <div class="fw-bold text-primary mb-1">{{ $entry->subject->name ?? '—' }}</div>
                                                    <div class="small text-muted">
                                                        @if($entry->teacher)
                                                            <i class="fas fa-user-tie me-1"></i> {{ $entry->teacher->first_name }} {{ $entry->teacher->family_name }}
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> بدون معلم</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px;">
            <i class="fas fa-table fa-2x text-secondary"></i>
        </div>
        <p class="mb-0">يرجى تحديد الشعبة من القائمة أعلاه لعرض جدولها الأسبوعي.</p>
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

    // Run on load
    if (classSelect.value) {
        filterSections();
    }
});
</script>
@endpush
