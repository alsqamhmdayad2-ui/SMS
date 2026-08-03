@extends('layouts.app')
@section('title', 'إدارة الاختبارات')

@section('content')

<x-page-header 
    title="الاختبارات والتقييمات">
    <x-slot:actions>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إنشاء اختبار جديد</a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'الاختبارات']
]" />

<x-shared.card title="قائمة الاختبارات">
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>الاختبار</th>
            <th>المادة والشعبة</th>
            <th>التاريخ والوقت</th>
            <th class="text-center">الحالة</th>
            <th class="text-center">الإجراءات</th>
        </x-slot:header>
        
        <x-slot:body>
            @forelse($exams as $exam)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $exam->title }}</div>
                        <x-shared.badge type="primary" class="text-capitalize small">{{ $exam->type }}</x-shared.badge>
                    </td>
                    <td>
                        <div class="small fw-bold">{{ $exam->subject->name }}</div>
                        <div class="small text-sms-muted">{{ $exam->schoolClass->name }} ({{ $exam->section->name }})</div>
                        <div class="small text-sms-muted"><i class="fas fa-user me-1"></i>{{ $exam->teacher->name }}</div>
                    </td>
                    <td>
                        <div><i class="fas fa-calendar me-1 text-sms-muted"></i>{{ $exam->exam_date?->format('d M Y') ?? '—' }}</div>
                        <div class="small text-sms-muted">
                            <i class="fas fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($exam->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                        </div>
                        @if($exam->duration_minutes)
                            <div class="small text-sms-primary"><i class="fas fa-hourglass-half me-1"></i>{{ $exam->duration_formatted }}</div>
                        @endif
                    </td>

                    <td class="text-center">
                        <x-shared.badge :type="$exam->status->badgeColor()" pill="true">{{ $exam->status->label() }}</x-shared.badge>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            <a href="{{ route('admin.exams.show', $exam->id) }}" class="btn btn-sm btn-outline-info" title="منشئ الاختبار">
                                <i class="fas fa-cog"></i> منشئ
                            </a>
                            <a href="{{ route('admin.exams.print', $exam->id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="طباعة">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
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
