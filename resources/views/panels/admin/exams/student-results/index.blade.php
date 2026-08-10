@extends('layouts.app')
@section('title', 'نتائج الطلاب')

@section('content')

<x-page-header title="نتائج الطلاب" />

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'نتائج الطلاب']
]" />

{{-- فلاتر البحث --}}
<x-shared.card class="mb-4 bg-sms-light" shadow="sm">
    <form action="{{ route('admin.students.result.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <x-form.input name="search" label="البحث عن طالب" placeholder="اكتب اسم الطالب..." value="{{ request('search') }}" />
        </div>
        <div class="col-md-3">
            <x-form.select name="class_id" label="الصف الدراسي" id="filterClass">
                <option value="">-- كل الصفوف --</option>
                @foreach($schoolClasses as $cls)
                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->grade->name ?? '' }} — {{ $cls->name }}
                    </option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-md-3">
            <x-form.select name="section_id" label="الشعبة" id="filterSection">
                <option value="">-- كل الشعب --</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}"
                        data-class="{{ $sec->class_id }}"
                        {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->schoolClass->name ?? '' }} - {{ $sec->name }}
                    </option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-md-2 mb-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i> بحث
            </button>
        </div>
    </form>
</x-shared.card>

<x-shared.card shadow="sm">
    <x-slot:header>
        <h6 class="m-0 fw-bold">
            <i class="fas fa-users me-2"></i> قائمة الطلاب
            <span class="badge bg-secondary ms-2">{{ $students->total() }}</span>
        </h6>
    </x-slot:header>
    <div class="row g-4 mt-3">
        @forelse($students as $student)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="avatar avatar-xl rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $student->name }}</h5>
                        <p class="text-muted small font-monospace mb-3">{{ $student->student_id ?? $student->id }}</p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-4 text-secondary small">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-school me-1"></i> {{ $student->section->schoolClass->name ?? ($student->schoolClass->name ?? '—') }}
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-layer-group me-1"></i> {{ $student->section->name ?? '—' }}
                            </span>
                        </div>
                        
                        <a href="{{ route('admin.students.result.show', $student->id) }}" class="btn btn-primary w-100 rounded-pill">
                            <i class="fas fa-file-alt me-1"></i> عرض النتائج
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <x-shared.empty-state title="لا يوجد طلاب" message="لم يتم العثور على أي طلاب بهذه المعايير." icon="person-badge" />
                </div>
            </div>
        @endforelse
    </div>

    @if($students->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $students->links() }}
        </div>
    @endif
</x-shared.card>

@push('styles')
<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>
@endpush

@endsection

@push('scripts')
<script>
document.getElementById('filterClass').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('filterSection');
    sectionSelect.querySelectorAll('option').forEach(opt => {
        if (!opt.value || !classId) { opt.style.display = ''; }
        else { opt.style.display = opt.dataset.class === classId ? '' : 'none'; }
    });
    sectionSelect.value = '';
});
</script>
@endpush
