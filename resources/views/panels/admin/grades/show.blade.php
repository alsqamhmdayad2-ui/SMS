@extends('layouts.app')
@section('title', 'تفاصيل المرحلة الدراسية')

@section('content')

<x-page-header 
    title="المرحلة الدراسية: {{ $grade->name }}">
    <x-slot:actions>
        <a href="{{ route('admin.grades.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
        <a href="{{ route('admin.grades.edit', $grade->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> تعديل</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المراحل الدراسية', 'url' => route('admin.grades.index')],
    ['name' => 'تفاصيل المرحلة']
]" />

<div class="row g-4">
    <div class="col-md-4">
        <x-shared.card shadow="sm" class="h-100">
            <div class="text-center p-4">
                <div class="rounded-circle bg-sms-primary bg-opacity-10 text-sms-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:100px;height:100px;font-size:3rem;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $grade->name }}</h4>
                <p class="text-sms-muted small mb-0">{{ $grade->notes ?? 'لا توجد ملاحظات' }}</p>
            </div>
            
            <x-slot:footerSlot>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-sms-muted"><i class="fas fa-chalkboard me-2"></i> عدد الصفوف</span>
                        <x-shared.badge type="primary" pill="true">{{ collect($grade->classes)->count() ?? 0 }}</x-shared.badge>
                    </li>
                </ul>
            </x-slot:footerSlot>
        </x-shared.card>
    </div>
    
    <div class="col-md-8">
        <x-shared.card shadow="sm" title="الصفوف الدراسية التابعة" icon="fas fa-chalkboard">
            <x-table.data-table hover="true">
                <x-slot:header>
                    <th>اسم الصف</th>
                    <th class="text-center">عدد الشعب</th>
                    <th class="text-center">إجراءات</th>
                </x-slot:header>
                
                <x-slot:body>
                    @forelse($grade->classes ?? [] as $class)
                        <tr>
                            <td class="fw-bold">{{ $class->name }}</td>
                            <td class="text-center">
                                <x-shared.badge type="info" class="text-white">{{ $class->sections()->count() ?? 0 }} شعبة</x-shared.badge>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-cog"></i> إدارة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-sms-muted">
                                لم يتم إضافة أي صفوف لهذه المرحلة بعد.
                            </td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>
        </x-shared.card>
    </div>
</div>

@endsection
