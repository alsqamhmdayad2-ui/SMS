@extends('layouts.app')
@section('title', 'بيانات ولي الأمر')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-tie me-2"></i> بيانات ولي الأمر</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-secondary">
            <i class="fas fa-edit me-1"></i> تعديل
        </a>
        <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>
</div>

@php
    $displayName = $parent->first_name
        ? trim("{$parent->first_name} {$parent->father_name} {$parent->grandfather_name} {$parent->family_name}")
        : ($parent->full_name ?? 'غير محدد');
    $initial = mb_substr($displayName, 0, 1);
    $types = ['Father' => 'الأب', 'Mother' => 'الأم', 'Guardian' => 'وصي قانوني'];
@endphp

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white mx-auto mb-3"
                     style="width:120px;height:120px;font-size:3rem;background:var(--gradient-primary);">
                    {{ $initial }}
                </div>
                <h4 class="fw-bold mb-1">{{ $displayName }}</h4>
                <p class="text-muted mb-2">{{ $types[$parent->guardian_type] ?? $parent->guardian_type }}</p>
                <p class="text-muted small mb-3">{{ $parent->occupation ?? '' }}</p>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">نشط</span>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted small"><i class="fas fa-id-card me-2"></i> رقم الهوية</span>
                        <span class="fw-semibold" dir="ltr">{{ $parent->national_id ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted small"><i class="fas fa-phone me-2"></i> الجوال الأول</span>
                        <span class="fw-semibold" dir="ltr">{{ $parent->phone_1 ?? $parent->phone ?? '-' }}</span>
                    </li>
                    @if($parent->phone_2)
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted small"><i class="fas fa-phone me-2"></i> الجوال الثاني</span>
                        <span class="fw-semibold" dir="ltr">{{ $parent->phone_2 }}</span>
                    </li>
                    @endif
                    @if($parent->workplace)
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted small"><i class="fas fa-briefcase me-2"></i> جهة العمل</span>
                        <span class="fw-semibold">{{ $parent->workplace }}</span>
                    </li>
                    @endif
                    @if($parent->address)
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted small"><i class="fas fa-map-marker-alt me-2"></i> العنوان</span>
                        <span class="fw-semibold">{{ $parent->address }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h3><i class="fas fa-user-graduate me-2"></i> الأبناء المسجلون
                    <span class="badge bg-info bg-opacity-10 text-info ms-2">{{ $parent->students->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table data-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الطالب</th>
                                <th>الرقم الأكاديمي</th>
                                <th>المرحلة</th>
                                <th>الصف</th>
                                <th>الشعبة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parent->students as $student)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="fw-bold text-decoration-none">
                                            {{ $student->first_name }} {{ $student->father_name }}
                                            {{ $student->grandfather_name }} {{ $student->family_name }}
                                        </a>
                                    </td>
                                    <td class="text-muted">#{{ $student->student_number ?? $student->national_id }}</td>
                                    <td>{{ $student->grade->name ?? '-' }}</td>
                                    <td>{{ $student->schoolClass->name ?? '-' }}</td>
                                    <td>{{ $student->section->name ?? '-' }}</td>
                                    <td>
                                        @if($student->status == 'active' || $student->status === null)
                                            <span class="badge bg-success bg-opacity-10 text-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">غير نشط</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2 opacity-25 d-block"></i>
                                        لا يوجد طلاب مسجلون لهذا الولي
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
