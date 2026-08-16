@extends('layouts.app')
@section('title', 'نتائج الطالب: '.$student->name)

@section('content')

@push('styles')
<style>
    .glass-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 15px 35px rgba(30, 60, 114, 0.2);
        position: relative;
        overflow: hidden;
    }
    .glass-header::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        transform: rotate(30deg);
        pointer-events: none;
    }
    .glass-stat {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        transition: transform 0.3s ease;
    }
    .glass-stat:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15);
    }
    .glass-stat .stat-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }
    .glass-stat .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 0;
    }
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        padding: 1rem;
    }
    .progress-custom {
        height: 8px;
        border-radius: 4px;
        background-color: #f1f5f9;
        overflow: hidden;
        margin-top: 6px;
    }
    .progress-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }
    .progress-fill.success { background: linear-gradient(90deg, #10b981, #34d399); }
    .progress-fill.danger { background: linear-gradient(90deg, #ef4444, #f87171); }
    .progress-fill.warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    
    @media print {
        .glass-header {
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
            border: 2px solid #000 !important;
        }
        .glass-stat {
            background: #fff !important;
            border: 1px solid #ccc !important;
            color: #000 !important;
        }
        .chart-container { display: none !important; }
        .no-print { display: none !important; }
    }
</style>
@endpush

<x-page-header title="نتائج الطالب: {{ $student->name }}">
    <x-slot name="actions">
        <a href="{{ route('admin.students.result.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
        @if($result ?? false)
        <a href="{{ route('admin.students.result.print', ['student' => $student->id, 'academic_year_id' => $selectedYear, 'semester_id' => $selectedSemester]) }}"
           target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> طباعة / PDF
        </a>
        @endif
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'نتائج الطلاب', 'url' => route('admin.students.result.index')],
    ['name' => 'نتائج الطالب']
]" />

{{-- فلاتر العام والفصل --}}
<x-shared.card class="mb-4 bg-sms-light" shadow="sm">
    <form action="{{ route('admin.students.result.show', $student->id) }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <x-form.select name="academic_year_id" label="العام الدراسي" required="true">
                @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $selectedYear == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-md-4">
            <x-form.select name="semester_id" label="الفصل الدراسي">
                <option value="">السنة الكاملة</option>
                @foreach($semesters as $s)
                <option value="{{ $s->id }}" {{ $selectedSemester == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sync me-1"></i> تحميل</button>
        </div>
    </form>
</x-shared.card>

@if($result && count($result['subjects']) > 0)

{{-- Premium Header & Summary --}}
<div class="glass-header p-4 mb-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-4">
                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center shadow-sm"
                     style="width:80px;height:80px;font-size:2.5rem;">
                    <i class="fas fa-user-graduate text-white"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-white">{{ $student->name }}</h3>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-school me-1"></i>
                        {{ $student->section->schoolClass->name ?? ($student->schoolClass->name ?? 'غير محدد') }}
                        &mdash; {{ $student->section->name ?? 'غير محدد' }}
                        &nbsp;|&nbsp;
                        <i class="fas fa-id-card me-1"></i> {{ $student->student_id ?? $student->id }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            @if($result['summary']['status'] == 'passed')
                <div class="badge bg-success bg-opacity-25 border border-success text-white px-4 py-2 fs-5 rounded-pill shadow-sm">
                    <i class="fas fa-check-circle me-2"></i> نااااجح بتفوق
                </div>
            @elseif($result['summary']['status'] == 'failed')
                <div class="badge bg-danger bg-opacity-25 border border-danger text-white px-4 py-2 fs-5 rounded-pill shadow-sm">
                    <i class="fas fa-times-circle me-2"></i> يحتاج تحسين
                </div>
            @else
                <div class="badge bg-warning bg-opacity-25 border border-warning text-white px-4 py-2 fs-5 rounded-pill shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i> غير مكتمل
                </div>
            @endif
        </div>
    </div>

    {{-- Stats Cards moved into the glass header --}}
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="glass-stat">
                <div class="stat-label"><i class="fas fa-chart-line me-1"></i> المتوسط العام</div>
                <div class="stat-value text-warning">{{ $result['summary']['average_percentage'] }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="glass-stat">
                <div class="stat-label"><i class="fas fa-check-circle me-1"></i> مواد النجاح</div>
                <div class="stat-value text-success">{{ $result['summary']['passed'] }} <span class="fs-6 opacity-75 fw-normal">/ {{ $result['summary']['total_subjects'] }}</span></div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="glass-stat">
                <div class="stat-label"><i class="fas fa-times-circle me-1"></i> مواد الإخفاق</div>
                <div class="stat-value text-danger">{{ $result['summary']['failed'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    {{-- جدول نتائج المواد بالتفصيل --}}
    <div class="col-lg-12">
        <x-shared.card shadow="sm" class="h-100 border-0 rounded-4">
            <x-slot:header class="bg-white border-0 pt-4 pb-2">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-alt me-2 text-sms-primary"></i> السجل الأكاديمي (مكونات التقييم)</h6>
            </x-slot:header>
            <div class="table-responsive px-2 pb-3">
                <table class="table table-hover table-bordered align-middle mb-0 text-center">
                    <thead class="table-light text-sms-muted small text-uppercase">
                        <tr>
                            <th class="border-0 py-3 text-start" style="min-width: 150px;">المادة</th>
                            @php $firstSub = $result['subjects'][0] ?? null; @endphp
                            @if($firstSub)
                                @foreach($firstSub['components'] as $comp)
                                <th class="border-0 py-3">{{ $comp['name'] }}</th>
                                @endforeach
                            @endif
                            <th class="border-0 py-3">المجموع</th>
                            <th class="border-0 py-3">التقدير</th>
                            <th class="border-0 py-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($result['subjects'] as $subResult)
                        @php
                            $pct = $subResult['total_percentage'] ?? 0;
                            $fillClass = $pct >= 90 ? 'success' : ($pct >= 75 ? '' : ($pct >= 50 ? 'warning' : 'danger'));
                        @endphp
                        <tr>
                            <td class="py-3 text-start">
                                <div class="fw-bold text-dark">{{ $subResult['subject']->name }}</div>
                                <div class="small text-sms-muted mt-1">
                                    @if($subResult['is_finalized'])
                                        <span class="text-success"><i class="fas fa-lock me-1"></i> معتمدة</span>
                                    @endif
                                </div>
                            </td>
                            
                            @foreach($subResult['components'] as $comp)
                            <td class="py-3">
                                @if($comp['total'] > 0)
                                    @php
                                        $detailsHtml = "<ul class='list-unstyled mb-0 small text-start'>";
                                        foreach($comp['details'] as $detail) {
                                            $detailsHtml .= "<li><strong>" . htmlspecialchars($detail['exam_title']) . ":</strong> " . $detail['obtained'] . "/" . $detail['total'] . "</li>";
                                        }
                                        $detailsHtml .= "</ul>";
                                    @endphp
                                    <a tabindex="0" class="text-decoration-none fw-bold" role="button" 
                                       data-bs-toggle="popover" 
                                       data-bs-trigger="focus" 
                                       title="تفاصيل: {{ $comp['name'] }}" 
                                       data-bs-html="true" 
                                       data-bs-content="{{ $detailsHtml }}">
                                        {{ $comp['obtained'] }}
                                    </a>
                                @else
                                    <span class="text-sms-muted">—</span>
                                @endif
                            </td>
                            @endforeach

                            <td class="py-3 fw-bold text-dark fs-6">{{ $pct }}%</td>
                            <td class="py-3">
                                @if($subResult['letter_grade'])
                                    <span class="badge bg-{{ $subResult['is_passing'] ? 'success' : 'danger' }} bg-opacity-10 text-{{ $subResult['is_passing'] ? 'success' : 'danger' }} fw-bold fs-6">{{ $subResult['letter_grade'] }}</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($subResult['is_passing'] === true)
                                <div class="text-success"><i class="fas fa-check-circle fs-5"></i></div>
                                @elseif($subResult['is_passing'] === false)
                                <div class="text-danger"><i class="fas fa-times-circle fs-5"></i></div>
                                @else — @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-shared.card>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });
});
</script>
@endpush

@elseif(request()->has('academic_year_id'))
<div class="text-center py-5">
    <x-shared.empty-state icon="inbox" title="لا توجد نتائج" message="لا توجد نتائج لهذا الطالب في الفترة المحددة." />
</div>
@else
<div class="text-center py-5">
    <x-shared.empty-state icon="funnel" title="اختر العام الدراسي" message="اختر العام الدراسي لتحميل النتائج." />
</div>
@endif

@endsection
