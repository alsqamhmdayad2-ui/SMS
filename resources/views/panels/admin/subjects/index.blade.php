@extends('layouts.app')
@section('title', 'قائمة المواد الدراسية')

@section('content')

<x-page-header 
    title="المواد الدراسية">
    <x-slot:actions>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة مادة</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'المواد الدراسية']
]" />

<x-data-table>
    <x-slot name="header">سجل المواد الدراسية ({{ $subjects->total() }} مادة)</x-slot>
    <x-slot name="thead">
        <tr>
            <th>اسم المادة</th>
            <th>الرمز</th>
            <th>الصفوف المرتبطة</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </tr>
    </x-slot>
    
    <x-slot name="tbody">
        @forelse($subjects as $subject)
            <tr>
                <td>
                    <div class="fw-bold"><i class="fas fa-book text-primary me-2"></i>{{ $subject->name }}</div>
                </td>
                <td><span class="badge bg-light text-dark border font-monospace">{{ $subject->code }}</span></td>
                <td>
                    @foreach($subject->classes as $class)
                        <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">{{ $class->name }}</span>
                    @endforeach
                </td>
                <td class="text-center">
                    <x-status-badge :status="$subject->status ? 'active' : 'inactive'" />
                </td>
                <td class="text-center">
                    <x-action-buttons 
                        :showUrl="route('admin.subjects.show', $subject->id)"
                        :editUrl="route('admin.subjects.edit', $subject->id)"
                        :deleteUrl="route('admin.subjects.destroy', $subject->id)"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-empty-state 
                        title="لا توجد مواد دراسية" 
                        message="لم يتم إضافة أي مواد دراسية بعد."
                        icon="book" 
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>

    @if($subjects->hasPages())
        <x-slot name="footer">
            {{ $subjects->appends(['search' => $search ?? ''])->links() }}
        </x-slot>
    @endif
</x-data-table>

@endsection
