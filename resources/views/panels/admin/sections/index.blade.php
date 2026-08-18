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
        
        @if(method_exists($sections, 'hasPages') && $sections->hasPages())
            <x-slot:pagination>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100 px-3 py-2">
                    <small class="text-muted">
                        عرض {{ $sections->firstItem() }}–{{ $sections->lastItem() }} من {{ $sections->total() }} شعبة
                    </small>
                    <nav aria-label="pagination">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- السابق --}}
                            <li class="page-item {{ $sections->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $sections->previousPageUrl() }}" aria-label="السابق">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
            
                            @foreach($sections->getUrlRange(max(1, $sections->currentPage()-2), min($sections->lastPage(), $sections->currentPage()+2)) as $page => $url)
                                <li class="page-item {{ $page == $sections->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach
            
                            {{-- التالي --}}
                            <li class="page-item {{ !$sections->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $sections->nextPageUrl() }}" aria-label="التالي">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </x-slot:pagination>
        @endif
    </x-table.data-table>
</x-shared.card>

@endsection
