@extends('layouts.app')
@section('title', 'الاختبارات - الطالب')

@section('content')

<x-page-header title="الاختبارات">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الاختبارات']
]" />

<!-- التبويبات -->
<div class="card mb-4">
    <div class="card-body">
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-available">الاختبارات المتاحة</button>
            <button class="tab-btn" data-tab="tab-completed">الاختبارات المكتملة</button>
        </div>

        <!-- تبويب: الاختبارات المتاحة -->
        <div class="tab-content active" id="tab-available">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                @forelse($availableExams as $exam)
                <div class="col">
                    <div class="exam-card h-100 shadow-sm border-0">
                        <div class="exam-subject">
                            <i class="fas fa-book text-primary" style="margin-inline-end: 8px"></i>{{ $exam->subject?->name ?? 'عام' }}
                        </div>
                        <div class="exam-title fw-bold mt-2">{{ $exam->title }}</div>
                        <div class="exam-meta text-muted small mt-2 d-flex flex-column gap-1">
                            <span><i class="fas fa-clock text-secondary w-15px"></i> {{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'غير محدد' }}</span>
                            <span><i class="fas fa-question-circle text-secondary w-15px"></i> {{ $exam->questions->count() ?? $exam->question_count }} سؤال</span>
                            <span><i class="fas fa-star text-secondary w-15px"></i> {{ $exam->total_marks }} درجة</span>
                            @if($exam->exam_date)
                            <span><i class="fas fa-calendar-alt text-secondary w-15px"></i> {{ $exam->exam_date->format('Y-m-d') }} {{ $exam->start_time ? $exam->start_time : '' }}</span>
                            @endif
                        </div>
                        <div class="exam-status-bar mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="badge bg-info text-white">متاح الآن</span>
                            <button class="btn btn-sm btn-primary px-3" onclick="alert('واجهة أداء الاختبار قيد التطوير.')"><i class="fas fa-play me-1"></i> بدء</button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-smile fa-3x mb-3 d-block opacity-25"></i>
                        <h5>لا توجد اختبارات متاحة حالياً.</h5>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- تبويب: الاختبارات المكتملة -->
        <div class="tab-content" id="tab-completed">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الاختبار</th>
                            <th>المادة</th>
                            <th>النوع</th>
                            <th>الدرجة الكلية</th>
                            <th>درجتي</th>
                            <th>النسبة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($completedExams as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $item['exam']->title }}</td>
                            <td>{{ $item['exam']->subject?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ match($item['exam']->type) { 'quiz' => 'اختبار قصير', 'midterm' => 'نصف فصلي', 'final' => 'نهائي', 'assignment' => 'واجب', default => $item['exam']->type } }}
                                </span>
                            </td>
                            <td>{{ $item['exam']->total_marks }}</td>
                            <td class="fw-bold {{ $item['result']->percentage >= 50 ? 'text-success' : 'text-danger' }}">
                                {{ (float)$item['result']->marks_obtained }}
                            </td>
                            <td>
                                <span class="badge {{ $item['result']->percentage >= 50 ? 'bg-success' : 'bg-danger' }}">
                                    {{ (float)$item['result']->percentage }}%
                                </span>
                            </td>
                            <td class="small text-muted">{{ $item['result']->submitted_at ? $item['result']->submitted_at->format('Y-m-d') : ($item['exam']->exam_date ? $item['exam']->exam_date->format('Y-m-d') : '-') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                لا توجد اختبارات مكتملة.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
