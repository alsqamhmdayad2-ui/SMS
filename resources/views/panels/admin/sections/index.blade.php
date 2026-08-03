@extends('layouts.app')
@section('title', 'قائمة الشُعب الدراسية')

@section('content')

<x-page-header 
    title="الشُعب الدراسية">
    <x-slot:actions>
        <a href="{{ route('admin.sections.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة شعبة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'الشُعب الدراسية']
]" />

<x-shared.card title="سجل الشُعب الدراسية">
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>اسم الشعبة</th>
            <th>الصف التابع</th>
            <th>المرحلة الدراسية</th>
            <th class="text-center">الطاقة الاستيعابية</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        
        <x-slot:body>
            @forelse($sections as $section)
                <tr>
                    <td>
                        <div class="fw-bold text-sms-primary"><i class="fas fa-users me-2"></i>{{ $section->name }}</div>
                    </td>
                    <td>{{ $section->schoolClass->name ?? '—' }}</td>
                    <td>{{ $section->schoolClass->grade->name ?? '—' }}</td>
                    <td class="text-center">
                        <x-shared.badge type="light" class="text-dark border">{{ $section->capacity ?? 'غير محدد' }} طالب</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <x-shared.badge :type="$section->status ? 'success' : 'secondary'" pill="true">{{ $section->status ? 'نشط' : 'غير نشط' }}</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <x-table.table-actions 
                                viewUrl="{{ Route::has('admin.sections.show') ? route('admin.sections.show', $section->id) : null }}"
                                editUrl="{{ route('admin.sections.edit', $section->id) }}"
                                deleteUrl="{{ route('admin.sections.destroy', $section->id) }}"
                                deleteId="{{ $section->id }}"
                            >
                                <a href="{{ route('admin.students.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-info text-white" title="إضافة طالب">
                                    <i class="fas fa-user-plus"></i> إضافة طالب
                                </a>
                            </x-table.table-actions>
                        </div>
                    </td>
                </tr>
            @empty
                <!-- Empty state handled by component -->
            @endforelse
        </x-slot:body>
    </x-table.data-table>
</x-shared.card>

@endsection
