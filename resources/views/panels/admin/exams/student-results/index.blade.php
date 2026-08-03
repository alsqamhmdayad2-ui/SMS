@extends('layouts.app')
@section('title', 'نتائج الطلاب')

@section('content')

<x-page-header title="نتائج الطلاب" />

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'نتائج الطلاب']
]" />

<!-- بحث / تصفية -->
<x-shared.card class="mb-4 bg-light" shadow="sm">
    <form action="{{ route('admin.students.result.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-6">
            <x-form.input name="search" label="البحث عن طالب" placeholder="أدخل اسم الطالب..." value="{{ request('search') }}" />
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
        <h6 class="m-0 fw-bold">قائمة الطلاب</h6>
    </x-slot:header>
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>رقم الطالب</th>
            <th>الاسم</th>
            <th>المرحلة</th>
            <th>الصف والشعبة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        <x-slot:body>
            @forelse($students as $student)
                <tr>
                    <td class="text-sms-muted font-monospace">#{{ $student->id }}</td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td>{{ $student->grade->name ?? '—' }}</td>
                    <td>{{ ($student->schoolClass->name ?? '—') . ' - ' . ($student->section->name ?? '—') }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.students.result.show', $student->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-alt me-1"></i> عرض النتائج
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <x-shared.empty-state title="لا يوجد طلاب" message="لم يتم العثور على أي طلاب." icon="person-badge" />
                    </td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-table.data-table>
    @if($students->hasPages())
        <div class="mt-3">
            {{ $students->links() }}
        </div>
    @endif
</x-shared.card>

@endsection
