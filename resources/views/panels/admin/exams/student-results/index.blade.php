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
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>الرقم</th>
            <th>الاسم الكامل</th>
            <th>الصف</th>
            <th>الشعبة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        <x-slot:body>
            @forelse($students as $student)
                <tr>
                    <td class="text-sms-muted font-monospace small">{{ $student->student_id ?? $student->id }}</td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td>{{ $student->section->schoolClass->name ?? ($student->schoolClass->name ?? '—') }}</td>
                    <td>{{ $student->section->name ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.students.result.show', $student->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-alt me-1"></i> عرض النتائج
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <x-shared.empty-state title="لا يوجد طلاب" message="لم يتم العثور على أي طلاب بهذه المعايير." icon="person-badge" />
                    </td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-table.data-table>
    @if($students->hasPages())
        <div class="mt-3">{{ $students->links() }}</div>
    @endif
</x-shared.card>

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
