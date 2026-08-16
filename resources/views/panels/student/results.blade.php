@extends('layouts.app')
@section('title', 'النتائج والدرجات')

@push('styles')
<style>
    .summary-card {
        border-radius: 16px;
        padding: 1.4rem 1.2rem;
        text-align: center;
        color: white;
        box-shadow: 0 4px 20px rgba(0,0,0,.1);
        transition: transform .2s;
    }
    .summary-card:hover { transform: translateY(-4px); }
    .summary-card .s-num { font-size: 2.2rem; font-weight: 800; line-height: 1; }
    .summary-card .s-label { font-size: .82rem; opacity: .9; margin-top: 5px; }

    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 18px; border-radius: 30px; font-weight: 700;
        font-size: .95rem;
    }
    .status-passed  { background: #dcfce7; color: #166534; }
    .status-failed  { background: #fee2e2; color: #991b1b; }
    .status-pending { background: #fef3c7; color: #92400e; }

    .grade-badge {
        font-size: .9rem; font-weight: 700; padding: 5px 14px; border-radius: 20px;
    }
    .grade-a  { background: #dcfce7; color: #166534; }
    .grade-b  { background: #dbeafe; color: #1e40af; }
    .grade-c  { background: #fef9c3; color: #854d0e; }
    .grade-d  { background: #ffedd5; color: #9a3412; }
    .grade-f  { background: #fee2e2; color: #991b1b; }
    .grade-na { background: #f1f5f9; color: #64748b; }

    .subject-row td { vertical-align: middle; }
    .comp-progress { height: 6px; border-radius: 4px; }
    .filter-pill {
        display: inline-flex; gap: 4px; background: #f1f5f9;
        border-radius: 30px; padding: 4px;
    }
    .filter-pill a {
        padding: 5px 16px; border-radius: 24px; font-size: .85rem;
        text-decoration: none; color: #475569; transition: all .15s;
    }
    .filter-pill a.active { background: #fff; color: #1e40af; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
</style>
@endpush

@section('content')

<x-page-header title="النتائج والدرجات">
    <x-slot:actions>
        @if(!empty($resultData['subjects']))
        <a href="{{ route('admin.students.result.print', [
                'student' => $student->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $selectedSemester
            ]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> طباعة الشهادة
        </a>
        @endif
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'النتائج والدرجات']
]" />

@php
    $subjects   = $resultData['subjects'] ?? [];
    $summary    = $resultData['summary'] ?? [];
    $status     = $summary['status'] ?? 'pending';
    $avgPct     = $summary['average_percentage'] ?? 0;
    $passedCnt  = $summary['passed'] ?? 0;
    $failedCnt  = $summary['failed'] ?? 0;
    $totalSubj  = $summary['total_subjects'] ?? 0;
@endphp

{{-- ─── Student Info Bar ─── --}}
@if($student)
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg,#1e3a8a,#3b82f6); color:white;">
    <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center gap-4">
        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:55px;height:55px;font-size:1.5rem;font-weight:800;">
            {{ mb_substr($student->first_name, 0, 1) }}
        </div>
        <div>
            <div class="fw-bold fs-5">{{ $student->full_name }}</div>
            <div class="opacity-75 small">
                {{ $student->section->schoolClass->name ?? '' }} — {{ $student->section->name ?? '' }}
                &nbsp;|&nbsp; العام: {{ $academicYear->name ?? '-' }}
            </div>
        </div>
        @if($totalSubj > 0)
        <div class="ms-auto">
            @if($status === 'passed')
                <span class="status-pill status-passed"><i class="fas fa-check-circle"></i> ناجح</span>
            @elseif($status === 'failed')
                <span class="status-pill status-failed"><i class="fas fa-times-circle"></i> راسب</span>
            @else
                <span class="status-pill status-pending"><i class="fas fa-hourglass-half"></i> قيد المراجعة</span>
            @endif
        </div>
        @endif
    </div>
</div>
@endif

{{-- ─── Summary Cards ─── --}}
@if($totalSubj > 0)
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="summary-card" style="background: linear-gradient(135deg,#1e3a8a,#3b82f6);">
            <div class="s-num">{{ $totalSubj }}</div>
            <div class="s-label">إجمالي المواد</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="summary-card" style="background: linear-gradient(135deg,#166534,#22c55e);">
            <div class="s-num">{{ $passedCnt }}</div>
            <div class="s-label">مادة ناجح</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="summary-card" style="background: linear-gradient(135deg,#991b1b,#ef4444);">
            <div class="s-num">{{ $failedCnt }}</div>
            <div class="s-label">مادة راسب</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="summary-card" style="background: linear-gradient(135deg,#92400e,#f59e0b);">
            <div class="s-num">{{ $avgPct }}%</div>
            <div class="s-label">المعدل العام</div>
        </div>
    </div>
</div>
@endif

{{-- ─── Semester Filter ─── --}}
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <span class="text-muted small fw-bold">الفترة:</span>
    <div class="filter-pill">
        <a href="{{ route('student.results') }}" class="{{ is_null($selectedSemester) ? 'active' : '' }}">السنة كاملة</a>
        @foreach($semesters as $sem)
        <a href="{{ route('student.results', ['semester_id' => $sem->id]) }}"
           class="{{ $selectedSemester == $sem->id ? 'active' : '' }}">{{ $sem->name }}</a>
        @endforeach
    </div>
</div>

{{-- ─── Results Table ─── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-2">
        <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar text-primary me-2"></i> تفاصيل المواد الدراسية</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-end pe-4">المادة</th>
                        <th>الدرجة / 100</th>
                        <th>النسبة</th>
                        <th>التقدير</th>
                        <th>الحالة</th>
                        <th>تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $sub)
                    <tr class="subject-row">
                        <td class="text-end pe-4 fw-bold">{{ $sub['subject']->name }}</td>
                        <td>
                            <span class="fw-bold fs-6">{{ $sub['total_percentage'] }}</span>
                            <small class="text-muted">/100</small>
                        </td>
                        <td>
                            <div style="width:80px; margin:auto;">
                                <div class="progress comp-progress">
                                    <div class="progress-bar {{ $sub['is_passing'] ? 'bg-success' : 'bg-danger' }}"
                                         style="width:{{ $sub['total_percentage'] }}%"></div>
                                </div>
                                <small class="text-muted">{{ $sub['total_percentage'] }}%</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $pct = $sub['total_percentage'];
                                $cls = $pct >= 90 ? 'grade-a' : ($pct >= 80 ? 'grade-b' : ($pct >= 70 ? 'grade-c' : ($pct >= 60 ? 'grade-d' : ($pct > 0 ? 'grade-f' : 'grade-na'))));
                            @endphp
                            <span class="grade-badge {{ $cls }}">{{ $sub['letter_grade'] ?? '-' }}</span>
                        </td>
                        <td>
                            @if($sub['is_passing'])
                                <span class="badge" style="background:#dcfce7;color:#166534;">ناجح</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;">راسب</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                    onclick='showDetails(@json($sub))'>
                                <i class="fas fa-eye me-1"></i> تفاصيل
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                            <h6>لا توجد نتائج معتمدة حتى الآن</h6>
                            <small>ستظهر نتائجك هنا بعد إعتمادها من قِبل الإدارة.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ─── Grade Details Modal ─── --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalSubjectName">
                    <i class="fas fa-book-open text-primary me-2"></i>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalBody"></div>
            <div class="modal-footer border-0 bg-light">
                <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showDetails(data) {
    document.getElementById('modalSubjectName').innerHTML =
        `<i class="fas fa-book-open text-primary me-2"></i>${data.subject.name}`;

    let html = '<ul class="list-group list-group-flush mb-3">';
    data.components.forEach(c => {
        const pct = c.total > 0 ? Math.round((c.obtained / c.total) * 100) : 0;
        const color = pct >= 50 ? 'bg-success' : 'bg-danger';
        html += `
        <li class="list-group-item px-0">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold">${c.name}</span>
                <span class="text-dark fw-bold">${c.obtained ?? '-'} / ${c.total}</span>
            </div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar ${color}" style="width:${pct}%"></div>
            </div>
        </li>`;
    });
    html += `</ul>
    <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:#f1f5f9;">
        <span class="fw-bold">المجموع الكلي</span>
        <span class="fw-bold fs-5 ${data.is_passing ? 'text-success' : 'text-danger'}">${data.total_percentage}%</span>
    </div>`;

    document.getElementById('modalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
</script>
@endpush
