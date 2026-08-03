@extends('layouts.app')
@section('title', 'تفاصيل الصف الدراسي')

@section('content')

<x-page-header 
    title="الصف الدراسي: {{ $class->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
        <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> تعديل</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'الصفوف الدراسية', 'url' => route('admin.classes.index')],
    ['name' => 'تفاصيل الصف']
]" />

<div class="row g-4">
    <div class="col-md-4">
        <x-shared.card shadow="sm" class="h-100">
            <div class="text-center p-4">
                <div class="rounded-circle bg-sms-primary bg-opacity-10 text-sms-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:100px;height:100px;font-size:3rem;">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $class->name }}</h4>
                <p class="text-sms-muted small mb-3">{{ $class->description ?? 'لا يوجد وصف' }}</p>
                <x-shared.badge :type="$class->status ? 'success' : 'secondary'" pill="true">{{ $class->status ? 'نشط' : 'غير نشط' }}</x-shared.badge>
            </div>
            
            <x-slot:footerSlot>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-sms-muted"><i class="fas fa-layer-group me-2"></i> المرحلة</span>
                        <span class="fw-semibold">{{ $class->grade->name ?? '—' }}</span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-sms-muted"><i class="fas fa-calendar-alt me-2"></i> العام الدراسي</span>
                        <x-shared.badge type="light" class="text-dark border">{{ $class->academicYear->name ?? '—' }}</x-shared.badge>
                    </li>
                </ul>
            </x-slot:footerSlot>
        </x-shared.card>
    </div>
    
    <div class="col-md-8">
        <x-shared.card shadow="sm" title="الشعب الدراسية التابعة" icon="fas fa-users">
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>اسم الشعبة</th>
                    <th>الطاقة الاستيعابية</th>
                    <th class="text-center">إجراءات</th>
                </x-slot:header>
                
                <x-slot:body>
                    @forelse($class->sections ?? [] as $section)
                        <tr>
                            <td class="fw-bold">{{ $section->name }}</td>
                            <td>{{ $section->capacity ?? '—' }} طالب</td>
                            <td class="text-center">
                                <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-cog"></i> إدارة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-sms-muted">
                                لم يتم إضافة أي شعبة لهذا الصف بعد.
                            </td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>
        </x-shared.card>
    </div>
</div>

@endsection
