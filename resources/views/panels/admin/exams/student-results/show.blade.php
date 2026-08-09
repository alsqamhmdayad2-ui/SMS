@extends('layouts.app')
@section('title', 'نتائج الطالب: '.$student->name)

@section('content')

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

{{-- بطاقة ملخص الطالب --}}
<x-shared.card class="border-start border-sms-primary border-5 mb-4" shadow="sm">
    <div class="row align-items-center">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-sms-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px;font-size:1.5rem;">
                    <i class="fas fa-user-graduate text-sms-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">{{ $student->name }}</h4>
                    <p class="text-sms-muted mb-0 small">
                        <i class="fas fa-school me-1"></i>
                        <strong>الصف:</strong> {{ $student->section->schoolClass->name ?? ($student->schoolClass->name ?? 'غير محدد') }}
                        &mdash;
                        <strong>الشعبة:</strong> {{ $student->section->name ?? 'غير محدد' }}
                        &nbsp;|&nbsp;
                        <strong>الرقم الأكاديمي:</strong> {{ $student->student_id ?? $student->id }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="d-flex justify-content-end gap-4 text-center">
                <div>
                    <div class="text-sms-muted small fw-semibold text-uppercase">المعدل التراكمي</div>
                    <div class="fs-3 fw-bold text-sms-primary">{{ $result['summary']['overall_gpa'] ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sms-muted small fw-semibold text-uppercase">المتوسط</div>
                    <div class="fs-3 fw-bold text-sms-success">{{ $result['summary']['average_percentage'] }}%</div>
                </div>
                <div>
                    <div class="text-sms-muted small fw-semibold text-uppercase">الحالة</div>
                    @if($result['summary']['status'] == 'passed')
                        <div class="fs-3 fw-bold text-sms-success"><i class="fas fa-check-circle"></i> ناجح</div>
                    @elseif($result['summary']['status'] == 'failed')
                        <div class="fs-3 fw-bold text-sms-danger"><i class="fas fa-times-circle"></i> راسب</div>
                    @else
                        <div class="fs-3 fw-bold text-warning"><i class="fas fa-exclamation-circle"></i> غير مكتمل</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-shared.card>

{{-- جدول نتائج المواد --}}
<x-shared.card shadow="sm" class="mb-4">
    <x-slot:header>
        <h6 class="m-0 fw-bold"><i class="fas fa-list-alt me-2"></i> نتائج المواد الدراسية</h6>
    </x-slot:header>
    <x-table.data-table hover="true">
        <x-slot:header>
            <th>المادة</th>
            <th>تفاصيل الدرجات</th>
            <th class="text-center">المجموع %</th>
            <th class="text-center">التقدير</th>
            <th class="text-center">GPA</th>
            <th class="text-center">الترتيب في الشعبة</th>
            <th class="text-center">الحالة</th>
        </x-slot:header>
        <x-slot:body>
            @foreach($result['subjects'] as $subResult)
            @php
                $rowColor = '';
                if(($subResult['total_percentage'] ?? 0) >= 90) $rowColor = 'bg-sms-success bg-opacity-10';
                elseif($subResult['is_passing'] === false) $rowColor = 'bg-sms-danger bg-opacity-10';
            @endphp
            <tr class="{{ $rowColor }}">
                <td class="fw-bold">
                    {{ $subResult['subject']->name }}
                    @if($subResult['is_finalized'])
                    <br><span class="badge bg-secondary" style="font-size:0.65rem"><i class="fas fa-lock me-1"></i> مؤكّد</span>
                    @endif
                    @if(!$subResult['is_published'])
                    <br><span class="badge bg-warning text-dark" style="font-size:0.65rem"><i class="fas fa-eye-slash me-1"></i> غير منشور</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($subResult['components'] as $comp)
                        <div class="border rounded px-2 py-1 bg-white" style="font-size:0.78rem;">
                            <span class="text-sms-muted">{{ $comp['name'] }}:</span>
                            <strong>{{ $comp['obtained'] }}/{{ $comp['total'] }}</strong>
                            @if($comp['contribution'] !== null)
                                <span class="text-sms-primary">({{ $comp['contribution'] }}%)</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </td>
                <td class="text-center fs-5 fw-bold">{{ $subResult['total_percentage'] }}%</td>
                <td class="text-center">
                    @if($subResult['letter_grade'])
                    <x-shared.badge :type="$subResult['is_passing'] ? 'success' : 'danger'" class="fs-6">
                        {{ $subResult['letter_grade'] }}
                    </x-shared.badge>
                    @else — @endif
                </td>
                <td class="text-center fw-bold">{{ $subResult['gpa_points'] ?? '—' }}</td>
                <td class="text-center fw-bold text-sms-muted">
                    {{ ($subResult['rank'] ?? null) ? '#' . $subResult['rank'] : '—' }}
                </td>
                <td class="text-center">
                    @if($subResult['is_passing'] === true)
                    <x-shared.badge type="success">ناجح</x-shared.badge>
                    @elseif($subResult['is_passing'] === false)
                    <x-shared.badge type="danger">راسب</x-shared.badge>
                    @else — @endif
                </td>
            </tr>
            @endforeach
        </x-slot:body>
    </x-table.data-table>
</x-shared.card>

{{-- بطاقات الإحصاءات --}}
<div class="row text-center mb-4 g-3">
    <div class="col-md-3">
        <x-dashboard.stat-card class="text-center" title="عدد المواد" value="{{ $result['summary']['total_subjects'] }}" icon="fas fa-book" />
    </div>
    <div class="col-md-3">
        <x-dashboard.stat-card class="text-center" title="المواد الناجحة" value="{{ $result['summary']['passed'] }}" color="success" icon="fas fa-check-circle" />
    </div>
    <div class="col-md-3">
        <x-dashboard.stat-card class="text-center" title="المواد الراسبة" value="{{ $result['summary']['failed'] }}" color="danger" icon="fas fa-times-circle" />
    </div>
    <div class="col-md-3">
        <x-dashboard.stat-card class="text-center" title="المتوسط العام" value="{{ $result['summary']['average_percentage'] }}%" color="primary" icon="fas fa-chart-line" />
    </div>
</div>

@elseif(request()->has('academic_year_id'))
<div class="text-center py-5">
    <x-shared.empty-state icon="inbox" title="لا توجد نتائج" message="لا توجد نتائج لهذا الطالب في الفترة المحددة." />
</div>
@else
<div class="text-center py-5">
    <x-shared.empty-state icon="funnel" title="اختر العام الدراسي" message="اختر العام الدراسي لتحميل النتائج." />
</div>
@endif

</div>
@endsection
