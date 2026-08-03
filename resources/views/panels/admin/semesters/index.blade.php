@extends('layouts.app')

@section('title', 'الفصول الدراسية')

@section('content')

<x-page-header 
    title="الفصول الدراسية">
    <x-slot:actions>
        <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة فصل</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'الفصول الدراسية']
]" />

<x-shared.card title="سجل الفصول الدراسية">
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>اسم الفصل</th>
            <th>العام الأكاديمي</th>
            <th>تاريخ البدء</th>
            <th>تاريخ الانتهاء</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        
        <x-slot:body>
            @forelse($semesters as $semester)
                <tr>
                    <td>
                        <div class="fw-bold text-sms-primary"><i class="fas fa-calendar-check me-2"></i>{{ $semester->name }}</div>
                    </td>
                    <td><x-shared.badge type="light" class="text-dark border">{{ $semester->academicYear->name ?? '—' }}</x-shared.badge></td>
                    <td>{{ $semester->start_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $semester->end_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="text-center">
                        <x-shared.badge :type="$semester->status ? 'success' : 'secondary'" pill="true">{{ $semester->status ? 'نشط' : 'غير نشط' }}</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <x-table.table-actions 
                            editUrl="{{ route('admin.semesters.edit', $semester->id) }}"
                            deleteUrl="{{ route('admin.semesters.destroy', $semester->id) }}"
                            deleteId="{{ $semester->id }}"
                        />
                    </td>
                </tr>
            @empty
                <!-- Empty state handled by component -->
            @endforelse
        </x-slot:body>
    </x-table.data-table>
</x-shared.card>

@endsection
