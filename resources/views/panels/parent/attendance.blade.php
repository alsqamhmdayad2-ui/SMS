@extends('layouts.app')
@section('title', 'متابعة الغياب - ولي الأمر')

@section('content')

<x-page-header title="متابعة الغياب">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'متابعة الغياب']
]" />

@foreach($childrenData as $data)
@php
    $child = $data['child'];
    $stats = $data['stats'];
    $records = $data['records'];
@endphp
<div class="card mb-4 shadow-sm border-0 slide-up">
    <div class="card-header bg-white border-light pt-4 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="fas fa-user-graduate ms-2 text-primary"></i>{{ $child->name }}</h5>
        <span class="badge bg-success-subtle text-success px-3 py-2">حضور: {{ $stats['attendance_percentage'] ?? 0 }}%</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-secondary small fw-bold">
                        <th class="ps-4">التاريخ</th>
                        <th>اليوم</th>
                        <th>الحالة</th>
                        <th>الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="ps-4">{{ \Carbon\Carbon::parse($record->session->date)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->session->date)->translatedFormat('l') }}</td>
                            <td>
                                @if($record->status->value === 'present')
                                    <span class="badge bg-success">حاضر</span>
                                @elseif($record->status->value === 'absent')
                                    <span class="badge bg-danger">غائب</span>
                                @elseif($record->status->value === 'late')
                                    <span class="badge bg-warning text-dark">متأخر</span>
                                @elseif($record->status->value === 'excused')
                                    <span class="badge bg-info">بعذر</span>
                                @elseif($record->status->value === 'sick')
                                    <span class="badge bg-secondary">مرض</span>
                                @else
                                    <span class="badge bg-secondary">{{ $record->status->value }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fs-4 mb-2 d-block opacity-50"></i>
                                لا توجد سجلات حضور مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@if(empty($childrenData))
    <x-empty-state message="لا يوجد أبناء مسجلين حالياً أو لم يتم العثور على الابن المطلوب" icon="fas fa-clipboard-user" />
@endif

@endsection
