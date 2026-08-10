@extends('layouts.app')
@section('title', 'كشف الدرجات (Gradebook)')

@section('content')

<x-page-header title="كشف الدرجات" />

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'كشف الدرجات']
]" />

<div class="">

    <x-alerts />

    <!-- Filters -->
    <x-shared.card class="mb-4 bg-light" shadow="sm">
        <form action="{{ route('admin.gradebook.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <x-form.select name="academic_year_id" label="العام الدراسي" required="true">
                    <option value="">اختر...</option>
                    @foreach($academicYears as $y)
                    <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="semester_id" label="الفصل الدراسي">
                    <option value="">الكل</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="grade_id" label="المرحلة الدراسية" required="true">
                    <option value="">اختر...</option>
                    @foreach($grades as $g)
                    <option value="{{ $g->id }}" {{ request('grade_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="section_id" label="الشعبة" required="true">
                    <option value="">اختر...</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="subject_id" label="المادة" required="true">
                    <option value="">اختر...</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> عرض الدرجات</button>
            </div>
        </form>
    </x-shared.card>

    @if($stats)
    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="عدد الطلاب" value="{{ $stats['total_students'] }}" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="المتوسط" value="{{ $stats['average'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="الأعلى" value="{{ $stats['highest'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="الأدنى" value="{{ $stats['lowest'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="الوسيط" value="{{ $stats['median'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="نسبة النجاح" value="{{ $stats['pass_rate'] }}%" color="{{ $stats['pass_rate'] >= 80 ? 'success' : ($stats['pass_rate'] >= 50 ? 'warning' : 'danger') }}" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="عدد الراسبين" value="{{ $stats['fail_count'] }}" color="danger" />
        </div>
    </div>

    <!-- Gradebook Table -->
    <x-shared.card shadow="sm">
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>الرتبة</th>
                <th>اسم الطالب</th>
                @foreach($components as $comp)
                <th class="text-center">{{ $comp->name }}</th>
                @endforeach
                <th class="text-center">المجموع</th>
                <th class="text-center">التقدير</th>
                <th class="text-center">GPA</th>
                <th class="text-center">الحالة</th>
                <th class="text-center">الإجراءات</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($gradebook as $row)
                @php
                    $colorClass = '';
                    if($row['total_percentage'] >= 90) $colorClass = 'grade-excellent';
                    elseif($row['total_percentage'] >= 75) $colorClass = 'grade-good';
                    elseif($row['total_percentage'] >= 60) $colorClass = 'grade-average';
                    elseif($row['total_percentage'] > 0) $colorClass = 'grade-fail';
                @endphp
                <tr class="{{ $colorClass }}">
                    <td class="fw-bold">
                        @if($row['rank'] == 1) 🥇
                        @elseif($row['rank'] == 2) 🥈
                        @elseif($row['rank'] == 3) 🥉
                        @else {{ $row['rank'] }}
                        @endif
                    </td>
                    <td>
                        <strong>{{ $row['student']->name }}</strong>
                        @if($row['is_finalized'])
                        <x-shared.badge type="secondary" class="ms-1"><i class="bi bi-lock-fill"></i> معتمد</x-shared.badge>
                        @endif
                    </td>
                    @foreach($row['components'] as $cs)
                    <td class="text-center">
                        @if($cs['total'] > 0)
                        <span class="fw-bold">{{ $cs['obtained'] }}</span><span class="text-sms-muted">/{{ $cs['total'] }}</span>
                        <br><small class="text-sms-muted">{{ $cs['percentage'] ?? 0 }}%</small>
                        @else
                        <span class="text-sms-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-center"><h5 class="mb-0"><x-shared.badge type="primary">{{ $row['total_percentage'] }}%</x-shared.badge></h5></td>
                    <td class="text-center">
                        @if($row['letter_grade'])
                        <x-shared.badge :type="$row['is_passing'] ? 'success' : 'danger'" class="fs-6">{{ $row['letter_grade'] }}</x-shared.badge>
                        @else - @endif
                    </td>
                    <td class="text-center fw-bold">{{ $row['gpa_points'] ?? '-' }}</td>
                    <td class="text-center">
                        @if($row['is_passing'] === true)
                        <x-shared.badge type="success">ناجح</x-shared.badge>
                        @elseif($row['is_passing'] === false)
                        <x-shared.badge type="danger">راسب</x-shared.badge>
                        @else - @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.students.result.show', $row['student']->id) }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-eye"></i> عرض النتائج
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $components->count() + 7 }}" class="text-center py-5 text-sms-muted">لا توجد بيانات متاحة.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>
    @elseif(request()->hasAny(['academic_year_id','subject_id','section_id']))
    <div class="text-center py-5">
        <x-shared.empty-state icon="search" title="لا توجد نتائج" message="لم يتم العثور على أي نتائج تتطابق مع بحثك." />
    </div>
    @else
    <div class="text-center py-5">
        <x-shared.empty-state icon="funnel" title="اختر الفلاتر أولاً" message="قم باختيار الفلاتر بالأعلى لعرض كشف الدرجات." />
    </div>
    @endif
</div>
</div>
@endsection

@push('styles')
<style>
tr.grade-excellent td { background-color: rgba(25,135,84,.06); }
tr.grade-good td { background-color: rgba(13,110,253,.06); }
tr.grade-average td { background-color: rgba(255,193,7,.06); }
tr.grade-fail td { background-color: rgba(220,53,69,.06); }
</style>
@endpush
