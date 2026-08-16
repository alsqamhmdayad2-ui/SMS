@extends('layouts.app')
@section('title', 'تقرير الحضور والغياب')

@push('styles')
<style>
    .stat-card {
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform .2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card .stat-num { font-size: 2.5rem; font-weight: 800; line-height: 1; }
    .stat-card .stat-label { font-size: .85rem; opacity: .9; margin-top: 6px; }
    .progress-arc-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .attendance-badge { font-size: .78rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .badge-present { background: #dcfce7; color: #166534; }
    .badge-absent  { background: #fee2e2; color: #991b1b; }
    .badge-late    { background: #fef3c7; color: #92400e; }
    .filter-bar { background: #f8fafc; border-radius: 12px; padding: 1rem 1.25rem; }
</style>
@endpush

@section('content')

<x-page-header title="تقرير الحضور والغياب">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الحضور والغياب']
]" />

{{-- ─── Stats Row ─── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#1e3a8a,#3b82f6);">
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">إجمالي الحصص</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#166534,#22c55e);">
            <div class="stat-num">{{ $stats['present'] }}</div>
            <div class="stat-label">حاضر</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#991b1b,#ef4444);">
            <div class="stat-num">{{ $stats['absent'] }}</div>
            <div class="stat-label">غائب</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#92400e,#f59e0b);">
            <div class="stat-num">{{ $stats['percentage'] }}%</div>
            <div class="stat-label">نسبة الحضور</div>
        </div>
    </div>
</div>

{{-- ─── Progress Bar ─── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">نسبة الحضور للعام: {{ $academicYear->name ?? '-' }}</span>
            <span class="fw-bold {{ $stats['percentage'] >= 75 ? 'text-success' : 'text-danger' }}">
                {{ $stats['percentage'] }}%
                @if($stats['percentage'] >= 75)
                    <i class="fas fa-check-circle ms-1"></i>
                @else
                    <i class="fas fa-exclamation-triangle ms-1"></i>
                @endif
            </span>
        </div>
        <div class="progress rounded-pill" style="height: 14px;">
            <div class="progress-bar {{ $stats['percentage'] >= 75 ? 'bg-success' : 'bg-danger' }} rounded-pill"
                 role="progressbar" style="width: {{ $stats['percentage'] }}%"></div>
        </div>
        @if($stats['percentage'] < 75)
            <small class="text-danger mt-1 d-block">
                <i class="fas fa-info-circle me-1"></i>
                نسبة الحضور أقل من 75%، يرجى المتابعة مع الإدارة.
            </small>
        @endif
    </div>
</div>

{{-- ─── Filter ─── --}}
<div class="filter-bar mb-4">
    <form method="GET" action="{{ route('student.reports') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold mb-1">الفصل الدراسي</label>
            <select name="semester_id" class="form-select form-select-sm">
                <option value="">العام الكامل</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter me-1"></i> فلتر
            </button>
            <a href="{{ route('student.reports') }}" class="btn btn-outline-secondary btn-sm ms-1">إعادة تعيين</a>
        </div>
    </form>
</div>

{{-- ─── Records Table ─── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-2">
        <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check text-primary me-2"></i> سجل الحضور التفصيلي</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-end pe-4">التاريخ</th>
                        <th>المادة</th>
                        <th>الحصة</th>
                        <th>الحالة</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                    <tr>
                        <td class="text-end pe-4 text-muted" style="font-size:.9rem;">
                            {{ \Carbon\Carbon::parse($rec->session->date)->format('Y/m/d') }}
                        </td>
                        <td class="fw-semibold">{{ $rec->session->subject->name ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $rec->session->period_number }}</span></td>
                        <td>
                            @if($rec->status === 'present')
                                <span class="attendance-badge badge-present"><i class="fas fa-check me-1"></i> حاضر</span>
                            @elseif($rec->status === 'absent')
                                <span class="attendance-badge badge-absent"><i class="fas fa-times me-1"></i> غائب</span>
                            @elseif($rec->status === 'late')
                                <span class="attendance-badge badge-late"><i class="fas fa-clock me-1"></i> متأخر</span>
                            @else
                                <span class="attendance-badge bg-secondary text-white">{{ $rec->status }}</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:.85rem;">{{ $rec->remarks ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3 d-block opacity-25"></i>
                            لا توجد سجلات حضور لهذه الفترة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
