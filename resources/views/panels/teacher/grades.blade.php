@extends('layouts.app')
@section('title', 'رصد الدرجات')

@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="page-title">
        <h2>
            <i class="fas fa-chart-bar" style="margin-inline-start:10px;color:var(--secondary)"></i>
            رصد الدرجات
        </h2>
        <ul class="breadcrumb mt-2 mb-0">
            <li><a href="{{ route('teacher.dashboard') }}">لوحة التحكم</a></li>
            <li>رصد الدرجات</li>
        </ul>
    </div>
    {{-- Section Filter --}}
    <form method="GET" class="d-flex flex-wrap gap-2">
        <select name="section_id" class="form-select" style="width:180px" onchange="this.form.submit()">
            @foreach($sections as $sec)
                <option value="{{ $sec->id }}" {{ (request('section_id') ?? $sections->first()?->id) == $sec->id ? 'selected' : '' }}>
                    {{ $sec->schoolClass?->grade?->name ?? '' }} — {{ $sec->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

@if($sections->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-chart-bar fa-4x mb-3 d-block opacity-40"></i>
            <h5>لا توجد شعب مسندة إليك</h5>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="mb-0">
                    درجات شعبة
                    @if($selectedSection)
                        {{ $selectedSection->schoolClass?->grade?->name ?? '' }} — {{ $selectedSection->name }}
                    @endif
                </h3>
            </div>
            <div class="d-flex gap-2">
                <span class="badge badge-info">{{ $students->count() }} طالب</span>
                <span class="text-muted small align-self-center">قريباً: نظام رصد الدرجات</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table data-table border-top mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الطالب</th>
                            <th>اسم الطالب</th>
                            <th>الشعبة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $i => $student)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong style="direction:ltr;display:inline-block">
                                        {{ str_pad($student->id, 8, '0', STR_PAD_LEFT) }}
                                    </strong>
                                </td>
                                <td class="fw-semibold">{{ $student->full_name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $selectedSection?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle me-1"></i>منتظم
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 d-block opacity-40"></i>
                                    لا يوجد طلاب في هذه الشعبة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($students->isNotEmpty())
            <div class="card-footer bg-transparent">
                <div class="alert alert-info d-flex align-items-center gap-2 mb-0 py-2">
                    <i class="fas fa-info-circle fs-5"></i>
                    <span>نظام رصد الدرجات التفصيلي قيد التطوير. سيتم إضافة إمكانية إدخال الدرجات وتصديرها قريباً.</span>
                </div>
            </div>
        @endif
    </div>
@endif

@endsection
