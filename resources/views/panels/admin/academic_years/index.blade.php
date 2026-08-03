@extends('layouts.app')

@section('title', 'الأعوام الأكاديمية')

@section('content')

<x-page-header 
    title="الأعوام الأكاديمية">
    <x-slot:actions>
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة عام أكاديمي</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'الأعوام الأكاديمية']
]" />

<x-shared.card title="سجل الأعوام الأكاديمية">
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>العام الأكاديمي</th>
            <th>تاريخ البدء</th>
            <th>تاريخ الانتهاء</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        
        <x-slot:body>
            @forelse($academicYears as $academicYear)
                <tr>
                    <td>
                        <div class="fw-bold text-sms-primary"><i class="fas fa-calendar me-2"></i>{{ $academicYear->name }}</div>
                    </td>
                    <td>{{ $academicYear->start_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $academicYear->end_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="text-center">
                        <x-shared.badge :type="$academicYear->status ? 'success' : 'secondary'" pill="true">{{ $academicYear->status ? 'نشط' : 'غير نشط' }}</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <x-table.table-actions 
                            editUrl="{{ route('admin.academic-years.edit', $academicYear->id) }}"
                            deleteUrl="{{ route('admin.academic-years.destroy', $academicYear->id) }}"
                            deleteId="{{ $academicYear->id }}"
                        />
                    </td>
                </tr>
            @empty
                <!-- Empty state handled by component -->
            @endforelse
        </x-slot:body>

        @if(method_exists($academicYears, 'hasPages') && $academicYears->hasPages())
            <x-slot:pagination>
                {{ $academicYears->links() }}
            </x-slot:pagination>
        @endif
    </x-table.data-table>
</x-shared.card>

@endsection
