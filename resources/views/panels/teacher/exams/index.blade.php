@extends('layouts.app')
@section('title', 'اختباراتي')

@section('content')

<x-page-header title="اختباراتي">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> اختبار جديد
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي']
]" />

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="إجمالي الاختبارات" :value="$exams->total()" icon="fas fa-file-alt" color="primary" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="مسودة" :value="$exams->getCollection()->where('status.value', 'draft')->count()" icon="fas fa-pencil-alt" color="warning" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="منشورة" :value="$exams->getCollection()->where('status.value', 'published')->count()" icon="fas fa-check-circle" color="success" />
    </div>
    <div class="col-6 col-md-3">
        <x-dashboard.stat-card title="مغلقة" :value="$exams->getCollection()->where('status.value', 'locked')->count()" icon="fas fa-lock" color="info" />
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('teacher.exams.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">الشعبة</label>
                <select name="section_id" class="form-select form-select-sm">
                    <option value="">-- جميع الشعب --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->schoolClass?->name }} - {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">المادة</label>
                <select name="subject_id" class="form-select form-select-sm">
                    <option value="">-- جميع المواد --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">الحالة</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- جميع الحالات --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ match($status->value) { 'draft' => 'مسودة', 'published' => 'منشور', 'locked' => 'مغلق', default => $status->value } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> تصفية</button>
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Exams Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">الاختبار</th>
                        <th>المادة</th>
                        <th>الشعبة</th>
                        <th>تاريخ الاختبار</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $exam->title }}</div>
                            <small class="text-muted">{{ match($exam->type) { 'quiz' => 'اختبار قصير', 'midterm' => 'نصف فصلي', 'final' => 'نهائي', 'assignment' => 'واجب', default => $exam->type } }}</small>
                        </td>
                        <td>{{ $exam->subject?->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info rounded-pill">
                                {{ $exam->section?->schoolClass?->name }} - {{ $exam->section?->name }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $exam->exam_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            @php $st = $exam->status->value ?? 'draft'; @endphp
                            <span class="badge rounded-pill {{ match($st) { 'draft' => 'bg-warning text-dark', 'published' => 'bg-success', 'locked' => 'bg-secondary', default => 'bg-light text-dark' } }}">
                                {{ match($st) { 'draft' => 'مسودة', 'published' => 'منشور', 'locked' => 'مغلق', default => $st } }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">

                                {{-- عرض / إدخال الدرجات --}}
                                <a href="{{ route('teacher.exams.show', $exam) }}"
                                   class="btn btn-sm btn-primary"
                                   title="عرض الاختبار وإدخال الدرجات">
                                    <i class="fas fa-eye me-1"></i> عرض
                                </a>

                                @if($exam->status->value === 'draft')

                                {{-- إدارة الأسئلة --}}
                                <a href="{{ route('teacher.exams.questions.index', $exam) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="إدارة الأسئلة">
                                    <i class="fas fa-list-ol me-1"></i> الأسئلة
                                </a>

                                {{-- تعديل بيانات الاختبار --}}
                                <a href="{{ route('teacher.exams.edit', $exam) }}"
                                   class="btn btn-sm btn-outline-warning"
                                   title="تعديل بيانات الاختبار">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- نشر الاختبار --}}
                                <form action="{{ route('teacher.exams.publish', $exam) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من نشر الاختبار؟ لن تتمكن من تعديل الأسئلة بعد النشر.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="نشر الاختبار">
                                        <i class="fas fa-bullhorn"></i>
                                    </button>
                                </form>

                                {{-- حذف الاختبار --}}
                                <form action="{{ route('teacher.exams.destroy', $exam) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                @elseif($exam->status->value === 'published')

                                {{-- إدخال الدرجات فقط --}}
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i> منشور
                                </span>

                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد اختبارات. <a href="{{ route('teacher.exams.create') }}">أنشئ اختباراً الآن</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
        <div class="p-3 border-top">{{ $exams->links() }}</div>
        @endif
    </div>
</div>

@endsection
