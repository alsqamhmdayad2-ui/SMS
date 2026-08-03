@extends('layouts.app')
@section('title', 'طلابي')

@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="page-title">
        <h2>
            <i class="fas fa-user-graduate" style="margin-inline-start:10px;color:var(--secondary)"></i>
            طلابي
        </h2>
        <ul class="breadcrumb mt-2 mb-0">
            <li><a href="{{ route('teacher.dashboard') }}">لوحة التحكم</a></li>
            <li>طلابي</li>
        </ul>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" class="d-flex flex-wrap gap-2">
            <select name="section_id" class="form-select" style="width:160px" onchange="this.form.submit()">
                <option value="">جميع الشعب</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->schoolClass?->grade?->name ?? '' }} - {{ $sec->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Students Card --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">قائمة الطلاب</h3>
        <span class="badge badge-info">{{ $students->count() }} طالب</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table data-table border-top mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم الطالب</th>
                        <th>اسم الطالب</th>
                        <th>المرحلة</th>
                        <th>الصف</th>
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
                            <td>{{ $student->section?->schoolClass?->grade?->name ?? '—' }}</td>
                            <td>{{ $student->section?->schoolClass?->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $student->section?->name ?? '—' }}</span>
                            </td>
                            <td>
                                @if($student->status === 'active' || $student->status === 1 || $student->status === true)
                                    <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>منتظم</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-exclamation-triangle me-1"></i>غير منتظم</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-user-graduate fa-3x mb-3 d-block opacity-40"></i>
                                لا يوجد طلاب في هذه الشعبة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
