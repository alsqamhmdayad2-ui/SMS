@extends('layouts.app')
@section('title', 'التقارير - الطالب')

@section('content')

<x-page-header title="التقارير الأكاديمية">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'التقارير']
]" />

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 h-100 slide-up" style="animation-delay: 0.4s;">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold m-0"><i class="fas fa-file-invoice ms-2 text-primary"></i>تقارير الدرجات (الشهادات المعتمدة)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light">
                            <tr class="text-secondary small fw-bold">
                                <th>تاريخ الإصدار</th>
                                <th>الفترة</th>
                                <th>المعدل (GPA)</th>
                                <th>النسبة المئوية</th>
                                <th>الحالة الأكاديمية</th>
                                <th>الترتيب على الشعبة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportCards as $reportCard)
                                <tr>
                                    <td>{{ $reportCard->published_at ? \Carbon\Carbon::parse($reportCard->published_at)->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $reportCard->report_period === 'semester' ? 'نهاية الفصل' : ($reportCard->report_period === 'yearly' ? 'نهاية العام' : $reportCard->report_period) }}</td>
                                    <td><span class="fw-bold text-primary">{{ $reportCard->gpa ?? '-' }}</span></td>
                                    <td><span class="badge bg-success rounded-pill px-3">{{ $reportCard->total_percentage }}%</span></td>
                                    <td>
                                        @if($reportCard->academic_status === 'passed')
                                            <span class="badge bg-success-subtle text-success">ناجح</span>
                                        @elseif($reportCard->academic_status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger">راسب</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">{{ $reportCard->academic_status ?? 'غير محدد' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $reportCard->rank_in_section ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="تحميل">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="window.print()" title="طباعة">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fs-1 mb-3 d-block opacity-50"></i>
                                        لا توجد شهادات معتمدة حتى الآن
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
