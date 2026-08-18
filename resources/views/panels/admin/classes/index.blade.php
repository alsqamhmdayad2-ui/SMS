@extends('layouts.app')
@section('title', 'قائمة الصفوف الدراسية')

@section('content')

<x-page-header 
    title="الصفوف الدراسية">
    <x-slot:actions>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة صف</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الهيكل الأكاديمي'],
    ['name' => 'الصفوف الدراسية']
]" />

<x-shared.card title="سجل الصفوف الدراسية">
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>اسم الصف</th>
            <th>المرحلة الدراسية</th>
            <th>العام الأكاديمي</th>
            <th class="text-center">الشعب</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        
        <x-slot:body>
            @forelse($classes as $class)
                <tr>
                    <td>
                        <div class="fw-bold text-sms-primary"><i class="fas fa-chalkboard me-2"></i>{{ $class->name }}</div>
                        @if($class->description)
                            <div class="small text-sms-muted">{{ Str::limit($class->description, 30) }}</div>
                        @endif
                    </td>
                    <td>{{ $class->grade->name ?? '—' }}</td>
                    <td><x-shared.badge type="light" class="text-dark border">{{ $class->academicYear->name ?? '—' }}</x-shared.badge></td>
                    <td class="text-center">
                        <x-shared.badge type="info" class="text-white">{{ collect($class->sections)->count() ?? 0 }} شعبة</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <x-shared.badge :type="$class->status ? 'success' : 'secondary'" pill="true">{{ $class->status ? 'نشط' : 'غير نشط' }}</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <x-table.table-actions 
                                viewUrl="{{ Route::has('admin.classes.show') ? route('admin.classes.show', $class->id) : null }}"
                                editUrl="{{ route('admin.classes.edit', $class->id) }}"
                                deleteUrl="{{ route('admin.classes.destroy', $class->id) }}"
                                deleteId="{{ $class->id }}"
                            >
                                <a href="{{ route('admin.sections.create', ['class_id' => $class->id]) }}" class="btn btn-sm btn-info text-white" title="إضافة شعب">
                                    <i class="fas fa-plus"></i> إضافة شعب
                                </a>
                            </x-table.table-actions>
                        </div>
                    </td>
                </tr>
            @empty
                <!-- Empty state handled by component -->
            @endforelse
        </x-slot:body>

        @if(method_exists($classes, 'hasPages') && $classes->hasPages())
            <x-slot:pagination>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100 px-3 py-2">
                    <small class="text-muted">
                        عرض {{ $classes->firstItem() }}–{{ $classes->lastItem() }} من {{ $classes->total() }} صف
                    </small>
                    <nav aria-label="pagination">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- السابق --}}
                            <li class="page-item {{ $classes->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $classes->previousPageUrl() }}" aria-label="السابق">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
            
                            @foreach($classes->getUrlRange(max(1, $classes->currentPage()-2), min($classes->lastPage(), $classes->currentPage()+2)) as $page => $url)
                                <li class="page-item {{ $page == $classes->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach
            
                            {{-- التالي --}}
                            <li class="page-item {{ !$classes->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $classes->nextPageUrl() }}" aria-label="التالي">
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
