@extends('layouts.app')
@section('title', 'سجل الحضور والغياب')

@section('content')

<x-page-header title="سجل الحضور والغياب">
    <x-slot:actions>
        <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> العام الدراسي {{ $activeYear ? $activeYear->name : 'الحالي' }}</span>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'سجل الحضور والغياب']
]" />

<div class="row mb-4">
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card bg-primary text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-white-50">نسبة الحضور</h5>
                    <h2 class="mb-0 fw-bold">{{ $stats['attendance_percentage'] ?? 0 }}%</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card bg-success text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="fas fa-user-check"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-white-50">حاضر</h5>
                    <h2 class="mb-0 fw-bold">{{ $stats['present_count'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card bg-danger text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="fas fa-user-times"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-white-50">غائب</h5>
                    <h2 class="mb-0 fw-bold">{{ $stats['absent_count'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="fas fa-user-clock"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-white-50">متأخر / بعذر</h5>
                    <h2 class="mb-0 fw-bold">{{ ($stats['late_count'] ?? 0) + ($stats['excused_count'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-list text-primary me-2"></i> تفاصيل الحضور</h5>
    </div>
    <div class="card-body p-0">
        @if($attendanceRecords->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted fw-bold">لا توجد سجلات حضور</h5>
                <p class="text-muted mb-0">لم يتم تسجيل أي غياب أو تأخير حتى الآن.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">التاريخ</th>
                            <th>المادة</th>
                            <th>المعلم</th>
                            <th>الحالة</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceRecords as $record)
                            <tr>
                                <td class="ps-4">
                                    @if($record->session && $record->session->date)
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($record->session->date)->format('Y/m/d') }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($record->session->date)->translatedFormat('l') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $record->session && $record->session->subject ? $record->session->subject->name : '-' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2">
                                            <i class="fas fa-user-tie text-muted"></i>
                                        </div>
                                        <span>{{ $record->session && $record->session->teacher && $record->session->teacher->user ? $record->session->teacher->user->name : '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusValue = $record->status instanceof \App\Enums\AttendanceStatus ? $record->status->value : $record->status;
                                        $statusClass = match($statusValue) {
                                            'present' => 'bg-success',
                                            'absent' => 'bg-danger',
                                            'late' => 'bg-warning text-dark',
                                            'excused' => 'bg-info text-dark',
                                            default => 'bg-secondary'
                                        };
                                        $statusText = match($statusValue) {
                                            'present' => 'حاضر',
                                            'absent' => 'غائب',
                                            'late' => 'متأخر',
                                            'excused' => 'بعذر',
                                            default => $statusValue
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $statusText }}</span>
                                </td>
                                <td class="text-muted">{{ $record->remarks ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
