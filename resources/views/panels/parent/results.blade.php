@extends('layouts.app')
@section('title', 'شهادات الطلاب - ولي الأمر')

@section('content')

<x-page-header title="شهادات الطلاب">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'شهادات الطلاب']
]" />

@foreach($childrenData as $data)
@php
    $child = $data['child'];
    $resultData = $data['resultData'];
    $subjects = collect($resultData['subjects'] ?? [])->filter(fn($s) => $s['is_published'])->all();
    $summary = $resultData['summary'] ?? [];
@endphp
<div class="card mb-4 shadow-sm border-0 slide-up">
    <div class="card-header bg-white border-light pt-4 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="fas fa-user-graduate ms-2 text-primary"></i>{{ $child->name }}</h5>
        <div>
            <button class="btn btn-sm btn-primary" onclick="window.print()"><i class="fas fa-print ms-1"></i> طباعة / حفظ كـ PDF</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-light">
                    <tr class="text-secondary small fw-bold">
                        <th class="text-start ps-4">المادة</th>
                        <th>الدرجة</th>
                        <th>النسبة</th>
                        <th>التقدير</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subjectResult)
                        <tr>
                            <td class="text-start ps-4 fw-bold">{{ $subjectResult['subject']->name }}</td>
                            <td>{{ $subjectResult['total_percentage'] }} / 100</td>
                            <td>
                                <span class="badge {{ $subjectResult['is_passing'] ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                                    {{ $subjectResult['total_percentage'] }}%
                                </span>
                            </td>
                            <td><span class="fw-bold {{ $subjectResult['is_passing'] ? 'text-success' : 'text-danger' }}">{{ $subjectResult['letter_grade'] ?? '-' }}</span></td>
                            <td>
                                @if($subjectResult['is_passing'])
                                    <span class="badge bg-success-subtle text-success">ناجح</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">راسب</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fs-4 mb-2 d-block opacity-50"></i>
                                لا توجد نتائج معتمدة حتى الآن
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($subjects))
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td class="text-start ps-4">المعدل النهائي</td>
                        <td>-</td>
                        <td>
                            <span class="badge bg-primary rounded-pill px-3">
                                {{ $summary['average_percentage'] ?? 0 }}%
                            </span>
                        </td>
                        <td>
                            <span class="text-primary fw-bold">
                                GPA: {{ $summary['overall_gpa'] ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if(($summary['status'] ?? '') === 'passed')
                                <span class="badge bg-success">ناجح</span>
                            @elseif(($summary['status'] ?? '') === 'failed')
                                <span class="badge bg-danger">راسب</span>
                            @else
                                <span class="badge bg-warning text-dark">غير مكتمل</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endforeach

@if(empty($childrenData))
    <x-empty-state message="لا يوجد أبناء مسجلين حالياً أو لم يتم العثور على الابن المطلوب" icon="fas fa-poll-h" />
@endif

@endsection
