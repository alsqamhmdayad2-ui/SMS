@extends('layouts.app')
@section('title', 'بناء الاختبار — ' . $exam->title)

@section('content')

<x-page-header title="بناء الاختبار: {{ $exam->title }}">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
        @if($exam->status === \App\Enums\ExamStatus::DRAFT)
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
            <i class="fas fa-plus-circle me-1"></i> إضافة سؤال
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#questionBankModal">
            <i class="fas fa-database me-1"></i> استيراد من البنك
        </button>
        <form action="{{ route('teacher.exams.publish', $exam) }}" method="POST" class="d-inline"
              onsubmit="return confirm('هل أنت متأكد من نشر الاختبار؟ لن تتمكن من تعديل الأسئلة بعد النشر.')">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-bullhorn me-1"></i> نشر الاختبار
            </button>
        </form>
        @endif
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('teacher.dashboard')],
    ['name' => 'اختباراتي', 'url' => route('teacher.exams.index')],
    ['name' => $exam->title, 'url' => route('teacher.exams.show', $exam)],
    ['name' => 'بناء الاختبار']
]" />

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- ─── Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                    <i class="fas fa-list-ol fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">عدد الأسئلة</div>
                    <div class="fw-bold fs-4" id="questionCount">{{ $exam->questions->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                    <i class="fas fa-star fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">مجموع الدرجات</div>
                    <div class="fw-bold fs-4" id="totalMarksDisplay">{{ $exam->questions->sum('pivot.mark_override') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                    <i class="fas fa-book fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">المادة</div>
                    <div class="fw-bold small">{{ $exam->subject?->name }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                    <i class="fas fa-signal fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">الحالة</div>
                    @php $st = $exam->status->value; @endphp
                    <span class="badge fs-6 {{ match($st) { 'draft'=>'bg-warning text-dark','published'=>'bg-success','closed'=>'bg-danger', default=>'bg-secondary' } }}">
                        {{ match($st) { 'draft'=>'مسودة','published'=>'منشور','closed'=>'مغلق', default=>$st } }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Questions List (Full width) ─── --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ol me-2 text-primary"></i>أسئلة الاختبار الحالية</h6>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="searchExamQuestions" class="form-control form-control-sm" style="max-width:220px;" placeholder="بحث في الأسئلة...">
            @if($exam->status === \App\Enums\ExamStatus::DRAFT)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="fas fa-plus me-1"></i> إضافة سؤال
            </button>
            @endif
        </div>
    </div>
    <div class="card-body bg-light" id="questionsContainer" style="min-height: 350px;">
        @forelse($exam->questions as $index => $q)
        <div class="card mb-3 border-0 shadow-sm question-item" id="question-{{ $q->id }}" data-id="{{ $q->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                        <span class="btn btn-sm btn-light cursor-move drag-handle p-1" title="اسحب لإعادة الترتيب">
                            <i class="fas fa-grip-vertical"></i>
                        </span>
                        @endif
                        <span class="badge bg-secondary">سؤال {{ $index + 1 }}</span>
                        <span class="badge bg-info text-dark">{{ $q->type->label() }}</span>
                        <span class="badge bg-success">{{ (float)($q->pivot->mark_override ?? $q->mark) }} درجة</span>
                        <span class="badge bg-{{ $q->difficulty->badgeColor() }}">{{ $q->difficulty->label() }}</span>
                        <span class="badge bg-light text-muted border small">{{ $q->question_code }}</span>
                    </div>

                    @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-warning edit-question"
                                data-id="{{ $q->id }}"
                                data-type="{{ $q->type->value }}"
                                data-text="{{ $q->question_text }}"
                                data-mark="{{ $q->pivot->mark_override ?? $q->mark }}"
                                data-difficulty="{{ $q->difficulty->value }}"
                                data-options='@json($q->options)'
                                title="تعديل السؤال">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary duplicate-question" data-id="{{ $q->id }}" title="تكرار السؤال">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-question" data-id="{{ $q->id }}" title="حذف السؤال">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endif
                </div>

                <p class="mb-2 mt-2 fs-6 text-dark question-text-content">{{ $q->question_text }}</p>

                @if($q->type->value === 'mcq')
                <ul class="list-group list-group-flush mt-2 border rounded">
                    @foreach($q->options as $opt)
                    <li class="list-group-item py-2 {{ $opt->is_correct ? 'list-group-item-success fw-bold text-success' : '' }}">
                        @if($opt->is_correct)
                        <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                        <i class="far fa-circle text-muted me-2"></i>
                        @endif
                        {{ $opt->option_text }}
                    </li>
                    @endforeach
                </ul>
                @elseif($q->type->value === 'true_false')
                <div class="mt-2 p-2 bg-white border rounded">
                    @php $correctOption = $q->options->first(); @endphp
                    <span class="fw-bold text-success">
                        <i class="fas fa-check-circle me-1"></i> الإجابة الصحيحة:
                        {{ $correctOption && $correctOption->is_correct ? 'صح (True)' : 'خطأ (False)' }}
                    </span>
                </div>
                @elseif($q->type->value === 'matching')
                <div class="row mt-2 g-1">
                    @foreach($q->options as $opt)
                    <div class="col-12 col-md-6">
                        <div class="p-2 border bg-white rounded d-flex align-items-center justify-content-between">
                            <span class="small">{{ $opt->left_item }} <i class="fas fa-arrow-right mx-1 text-primary"></i> {{ $opt->right_item }}</span>
                            @if($opt->partial_mark)
                            <span class="badge bg-secondary">{{ (float)$opt->partial_mark }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @elseif(in_array($q->type->value, ['short_answer', 'essay', 'fill_blank']))
                @php $modelOpt = $q->options->first(); @endphp
                @if($modelOpt && $modelOpt->option_text)
                <div class="mt-2 p-2 bg-white border border-primary border-opacity-25 rounded">
                    <span class="text-muted small fw-bold"><i class="fas fa-lightbulb text-warning me-1"></i>
                    {{ $q->type->value === 'fill_blank' ? 'الإجابة الصحيحة:' : 'نموذج الإجابة:' }}
                    </span>
                    <span class="small ms-1">{{ $modelOpt->option_text }}</span>
                </div>
                @endif
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted" id="noQuestionsMsg">
            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
            <h6>لا توجد أسئلة مضافة بعد.</h6>
            <p class="small mb-3">اضغط على "إضافة سؤال" لإنشاء أسئلة جديدة، أو استورد من بنك الأسئلة.</p>
            @if($exam->status === \App\Enums\ExamStatus::DRAFT)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="fas fa-plus-circle me-1"></i> إضافة أول سؤال
            </button>
            @endif
        </div>
        @endforelse
    </div>
</div>

{{-- ─── Add / Edit Question Modal ─── --}}
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="fas fa-plus-circle me-2 text-primary"></i> إضافة سؤال جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    @csrf
                    <input type="hidden" name="question_id" id="editQuestionId" value="">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">نوع السؤال <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="questionType" required>
                                <option value="">اختر النوع...</option>
                                <option value="mcq">اختيار من متعدد (MCQ)</option>
                                <option value="true_false">صح / خطأ</option>
                                <option value="short_answer">إجابة قصيرة</option>
                                <option value="essay">مقال</option>
                                <option value="matching">توصيل / مطابقة</option>
                                <option value="fill_blank">إكمال الفراغ</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">الدرجة <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="0.5" class="form-control" name="mark" id="questionMark" value="1.0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">الصعوبة</label>
                            <select class="form-select" name="difficulty" id="questionDifficulty">
                                <option value="easy" selected>سهل</option>
                                <option value="medium">متوسط</option>
                                <option value="hard">صعب</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نص السؤال <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="question_text" id="questionText" rows="4" required placeholder="اكتب نص السؤال هنا..."></textarea>
                    </div>

                    {{-- Dynamic Fields (MCQ, True/False, Matching) --}}
                    <div id="dynamicFields" class="p-3 bg-light rounded mb-3 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" id="cancelEditBtn" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveQuestionBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    <span id="saveBtnText">حفظ السؤال</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── Question Bank Import Modal ─── --}}
<div class="modal fade" id="questionBankModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-database me-2 text-primary"></i>استيراد من بنك الأسئلة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input type="text" id="bankSearch" class="form-control form-control-sm" placeholder="بحث في الأسئلة...">
                    </div>
                    <div class="col-md-3">
                        <select id="bankTypeFilter" class="form-select form-select-sm">
                            <option value="">كل الأنواع</option>
                            <option value="mcq">اختيار من متعدد</option>
                            <option value="true_false">صح / خطأ</option>
                            <option value="short_answer">إجابة قصيرة</option>
                            <option value="essay">مقال</option>
                            <option value="matching">توصيل</option>
                            <option value="fill_blank">إكمال الفراغ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="bankDifficultyFilter" class="form-select form-select-sm">
                            <option value="">كل المستويات</option>
                            <option value="easy">سهل</option>
                            <option value="medium">متوسط</option>
                            <option value="hard">صعب</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="refreshBankBtn" title="تحديث"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div id="bankQuestionsList">
                    <div class="text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>افتح النافذة لتحميل الأسئلة.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- ─── Form Templates ─── --}}
<template id="mcq-form-template">
    <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-primary mb-0"><i class="fas fa-list-ol me-1"></i> خيارات الاختيار من متعدد</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addMcqOptionBtn"><i class="fas fa-plus-circle me-1"></i>إضافة خيار</button>
        </div>
        <div id="mcqContainer"></div>
    </div>
</template>

<template id="truefalse-form-template">
    <div class="mb-2">
        <h6 class="fw-bold text-primary mb-2"><i class="fas fa-question-circle me-1"></i>الإجابة الصحيحة</h6>
        <div class="d-flex gap-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_correct_boolean" id="tfTrue" value="1" required>
                <label class="form-check-label fw-bold text-success" for="tfTrue">صح (True)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_correct_boolean" id="tfFalse" value="0" required>
                <label class="form-check-label fw-bold text-danger" for="tfFalse">خطأ (False)</label>
            </div>
        </div>
    </div>
</template>

<template id="matching-form-template">
    <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-primary mb-0"><i class="fas fa-random me-1"></i>أزواج التوصيل</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addMatchPairBtn"><i class="fas fa-plus-circle me-1"></i>إضافة زوج</button>
        </div>
        <div id="matchingContainer"></div>
    </div>
</template>

<template id="essay-form-template">
    <div class="mb-2">
        <div class="alert alert-info small py-2 mb-3"><i class="fas fa-info-circle me-1"></i>سؤال مقالي — يمكنك كتابة نموذج إجابة للمرجعية عند التصحيح.</div>
        <label class="form-label fw-bold small">نموذج الإجابة <span class="text-muted fw-normal">(اختياري — للمرجعية فقط)</span></label>
        <textarea class="form-control" name="model_answer" id="modelAnswer" rows="3" placeholder="اكتب نموذج الإجابة المتوقعة هنا..."></textarea>
    </div>
</template>

<template id="fillblank-form-template">
    <div class="mb-2">
        <div class="alert alert-warning small py-2 mb-3"><i class="fas fa-fill-drip me-1"></i>استخدم <strong>___</strong> في نص السؤال للإشارة إلى مكان الفراغ. مثال: <em>عاصمة المملكة هي ___</em></div>
        <label class="form-label fw-bold small">الإجابة الصحيحة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="model_answer" id="modelAnswer" placeholder="اكتب الإجابة الصحيحة المتوقعة..." required>
        <div class="form-text">سيتم مقارنة إجابة الطالب بهذه الإجابة عند التصحيح.</div>
    </div>
</template>

<template id="shortanswer-form-template">
    <div class="mb-2">
        <div class="alert alert-info small py-2 mb-3"><i class="fas fa-pen me-1"></i>سؤال إجابة قصيرة — يمكنك تحديد الإجابة النموذجية للمرجعية.</div>
        <label class="form-label fw-bold small">الإجابة النموذجية <span class="text-muted fw-normal">(للمرجعية عند التصحيح)</span></label>
        <input type="text" class="form-control" name="model_answer" id="modelAnswer" placeholder="اكتب الإجابة النموذجية...">
    </div>
</template>

@endsection

@push('styles')
<style>
.cursor-move { cursor: move !important; }
.question-item { transition: all 0.2s ease; }
.question-item.sortable-ghost {
    opacity: 0.4;
    background-color: #e9ecef !important;
    border: 2px dashed #0d6efd !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const CSRF       = '{{ csrf_token() }}';
    const BASE_URL   = '{{ url("teacher/exams/" . $exam->id . "/questions") }}';
    const REORDER_URL= '{{ route("teacher.exams.questions.reorder", $exam) }}';
    const BANK_URL   = '{{ route("teacher.exams.questions.bank", $exam) }}';
    const IMPORT_URL = '{{ route("teacher.exams.questions.import", $exam) }}';

    const headers = {
        'X-CSRF-TOKEN': CSRF,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    };

    const typeSelect         = document.getElementById('questionType');
    const dynamicFields      = document.getElementById('dynamicFields');
    const form               = document.getElementById('questionForm');
    const saveBtn            = document.getElementById('saveQuestionBtn');
    const saveBtnText        = document.getElementById('saveBtnText');
    const modalTitle         = document.getElementById('modalTitle');
    const cancelEditBtn      = document.getElementById('cancelEditBtn');
    const editQuestionId     = document.getElementById('editQuestionId');
    const questionsContainer = document.getElementById('questionsContainer');
    const addModal           = document.getElementById('addQuestionModal');
    const bsAddModal         = new bootstrap.Modal(addModal);

    // Reset form when modal is closed
    addModal.addEventListener('hidden.bs.modal', resetForm);

    // ── 1. Dynamic Form Switching ─────────────────────────────────────────────
    typeSelect.addEventListener('change', () => renderFormForType(typeSelect.value));

    function renderFormForType(type, optionsData = null) {
        dynamicFields.innerHTML = '';
        if (!type) { dynamicFields.classList.add('d-none'); return; }
        dynamicFields.classList.remove('d-none');

        if (type === 'mcq') {
            dynamicFields.innerHTML = document.getElementById('mcq-form-template').innerHTML;
            setupMcqForm(optionsData);
        } else if (type === 'true_false') {
            dynamicFields.innerHTML = document.getElementById('truefalse-form-template').innerHTML;
            if (optionsData?.length > 0) {
                document.getElementById(optionsData[0].is_correct ? 'tfTrue' : 'tfFalse').checked = true;
            }
        } else if (type === 'matching') {
            dynamicFields.innerHTML = document.getElementById('matching-form-template').innerHTML;
            setupMatchingForm(optionsData);
        } else if (type === 'short_answer') {
            dynamicFields.innerHTML = document.getElementById('shortanswer-form-template').innerHTML;
            if (optionsData?.length > 0 && optionsData[0].option_text) {
                const field = document.getElementById('modelAnswer');
                if (field) field.value = optionsData[0].option_text;
            }
        } else if (type === 'essay') {
            dynamicFields.innerHTML = document.getElementById('essay-form-template').innerHTML;
            if (optionsData?.length > 0 && optionsData[0].option_text) {
                const field = document.getElementById('modelAnswer');
                if (field) field.value = optionsData[0].option_text;
            }
        } else if (type === 'fill_blank') {
            dynamicFields.innerHTML = document.getElementById('fillblank-form-template').innerHTML;
            if (optionsData?.length > 0 && optionsData[0].option_text) {
                const field = document.getElementById('modelAnswer');
                if (field) field.value = optionsData[0].option_text;
            }
        } else {
            dynamicFields.classList.add('d-none');
        }
    }

    function setupMcqForm(optionsData = null) {
        const container = document.getElementById('mcqContainer');
        const addBtn    = document.getElementById('addMcqOptionBtn');
        if (optionsData?.length > 0) {
            optionsData.forEach((opt, idx) => addMcqOptionRow(container, idx, opt.option_text, opt.is_correct));
        } else {
            addMcqOptionRow(container, 0, '', true);
            addMcqOptionRow(container, 1, '', false);
        }
        addBtn.addEventListener('click', () => addMcqOptionRow(container, container.children.length, '', false));
    }

    function addMcqOptionRow(container, index, val = '', checked = false) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 mcq-option-row';
        div.innerHTML = `
            <div class="input-group-text bg-white">
                <input class="form-check-input mt-0" type="radio" name="correct_option_index" value="${index}" required ${checked ? 'checked' : ''}>
            </div>
            <input type="text" class="form-control" name="options[]" value="${val}" placeholder="الخيار ${index + 1}" required>
            ${index > 1 ? '<button class="btn btn-outline-danger remove-btn" type="button"><i class="fas fa-times"></i></button>' : ''}
        `;
        container.appendChild(div);
        div.querySelector('.remove-btn')?.addEventListener('click', () => {
            div.remove();
            container.querySelectorAll('input[type="radio"]').forEach((r, i) => r.value = i);
        });
    }

    function setupMatchingForm(optionsData = null) {
        const container = document.getElementById('matchingContainer');
        const addBtn    = document.getElementById('addMatchPairBtn');
        if (optionsData?.length > 0) {
            optionsData.forEach((opt, idx) => addMatchingRow(container, idx, opt.left_item, opt.right_item, opt.partial_mark));
        } else {
            addMatchingRow(container, 0);
            addMatchingRow(container, 1);
        }
        addBtn.addEventListener('click', () => addMatchingRow(container, container.querySelectorAll('.match-row').length));
    }

    function addMatchingRow(container, index, left = '', right = '', partial = '') {
        const div = document.createElement('div');
        div.className = 'row mb-2 match-row align-items-center g-1';
        div.innerHTML = `
            <div class="col-4"><input type="text" class="form-control" name="pairs[${index}][left]" value="${left}" placeholder="العنصر الأيمن" required></div>
            <div class="col-1 text-center"><i class="fas fa-arrow-right text-muted small"></i></div>
            <div class="col-4"><input type="text" class="form-control" name="pairs[${index}][right]" value="${right}" placeholder="العنصر الأيسر" required></div>
            <div class="col-2"><input type="number" step="0.25" min="0" class="form-control" name="pairs[${index}][partial_mark]" value="${partial ?? ''}" placeholder="درجة جزئية"></div>
            <div class="col-1 text-end">${index > 1 ? '<button class="btn btn-sm btn-outline-danger remove-btn w-100" type="button"><i class="fas fa-times"></i></button>' : ''}</div>
        `;
        container.appendChild(div);
        div.querySelector('.remove-btn')?.addEventListener('click', () => {
            div.remove();
            container.querySelectorAll('.match-row').forEach((row, i) => {
                row.querySelectorAll('input').forEach(inp => {
                    const n = inp.getAttribute('name');
                    if (n) inp.setAttribute('name', n.replace(/pairs\[\d+\]/, `pairs[${i}]`));
                });
            });
        });
    }

    // ── 2. Drag & Drop Reorder ────────────────────────────────────────────────
    if (questionsContainer) {
        new Sortable(questionsContainer, {
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            animation: 150,
            onEnd: function () {
                const orderedIds = Array.from(questionsContainer.querySelectorAll('.question-item')).map(el => el.dataset.id);
                fetch(REORDER_URL, {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ordered_ids: orderedIds })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        questionsContainer.querySelectorAll('.question-item').forEach((card, idx) => {
                            const badge = card.querySelector('.badge.bg-secondary');
                            if (badge) badge.textContent = `سؤال ${idx + 1}`;
                        });
                    }
                });
            }
        });
    }

    // ── 3. Save / Update Question ─────────────────────────────────────────────
    saveBtn.addEventListener('click', function () {
        if (!form.reportValidity()) return;

        const spinner = saveBtn.querySelector('.spinner-border');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        const qId    = editQuestionId.value;
        const isEdit = !!qId;
        const url    = isEdit ? `${BASE_URL}/${qId}` : BASE_URL;
        const method = isEdit ? 'PUT' : 'POST';
        const formData = new FormData(form);

        let body, reqHeaders = { ...headers };
        if (isEdit) {
            const obj = {};
            formData.forEach((value, key) => {
                if (key.endsWith('[]')) {
                    const k = key.slice(0, -2);
                    if (!obj[k]) obj[k] = [];
                    obj[k].push(value);
                } else if (key.match(/^pairs\[/)) {
                    const m = key.match(/pairs\[(\d+)\]\[(\w+)\]/);
                    if (m) {
                        if (!obj['pairs']) obj['pairs'] = [];
                        if (!obj['pairs'][m[1]]) obj['pairs'][m[1]] = {};
                        obj['pairs'][m[1]][m[2]] = value;
                    }
                } else {
                    obj[key] = value;
                }
            });
            body = JSON.stringify(obj);
            reqHeaders['Content-Type'] = 'application/json';
        } else {
            body = formData;
        }

        fetch(url, { method, headers: reqHeaders, body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'خطأ أثناء حفظ السؤال');
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        })
        .catch(() => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // ── 4. Edit Mode ──────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-question');
        if (!btn) return;

        editQuestionId.value = btn.dataset.id;
        typeSelect.value = btn.dataset.type;
        document.getElementById('questionText').value = btn.dataset.text;
        document.getElementById('questionMark').value = btn.dataset.mark;
        document.getElementById('questionDifficulty').value = btn.dataset.difficulty;

        let options = [];
        try { options = JSON.parse(btn.dataset.options); } catch(e) {}
        renderFormForType(btn.dataset.type, options);

        modalTitle.innerHTML = '<i class="fas fa-pencil-alt me-2 text-warning"></i> تعديل السؤال';
        saveBtnText.textContent = 'تحديث السؤال';
        saveBtn.className = 'btn btn-warning';
        bsAddModal.show();
    });

    function resetForm() {
        form.reset();
        editQuestionId.value = '';
        dynamicFields.innerHTML = '';
        dynamicFields.classList.add('d-none');
        modalTitle.innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i> إضافة سؤال جديد';
        saveBtnText.textContent = 'حفظ السؤال';
        saveBtn.className = 'btn btn-primary';
        saveBtn.disabled = false;
        saveBtn.querySelector('.spinner-border').classList.add('d-none');
    }

    // ── 5. Duplicate Question ─────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.duplicate-question');
        if (!btn) return;
        btn.disabled = true;
        fetch(`${BASE_URL}/${btn.dataset.id}/duplicate`, { method: 'POST', headers })
        .then(r => r.json())
        .then(data => {
            if (data.success) window.location.reload();
            else { alert(data.message || 'فشل النسخ'); btn.disabled = false; }
        })
        .catch(() => btn.disabled = false);
    });

    // ── 6. Delete Question ────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-question');
        if (!btn) return;
        if (!confirm('هل أنت متأكد من حذف هذا السؤال؟')) return;
        btn.disabled = true;
        fetch(`${BASE_URL}/${btn.dataset.id}`, { method: 'DELETE', headers })
        .then(r => r.json())
        .then(data => {
            if (data.success) window.location.reload();
            else { alert(data.message || 'فشل الحذف'); btn.disabled = false; }
        })
        .catch(() => btn.disabled = false);
    });

    // ── 7. Question Bank Modal ────────────────────────────────────────────────
    const bankModal  = document.getElementById('questionBankModal');
    const bankList   = document.getElementById('bankQuestionsList');
    const bankSearch = document.getElementById('bankSearch');
    const bankType   = document.getElementById('bankTypeFilter');
    const bankDiff   = document.getElementById('bankDifficultyFilter');
    const refreshBtn = document.getElementById('refreshBankBtn');

    if (bankModal) {
        bankModal.addEventListener('show.bs.modal', loadBank);
        bankSearch.addEventListener('input', debounce(loadBank, 400));
        bankType.addEventListener('change', loadBank);
        bankDiff.addEventListener('change', loadBank);
        refreshBtn.addEventListener('click', loadBank);
    }

    function loadBank() {
        bankList.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>جاري التحميل...</div>';
        const params = new URLSearchParams({ search: bankSearch.value, type: bankType.value, difficulty: bankDiff.value });
        fetch(`${BANK_URL}?${params}`, { method: 'GET', headers })
        .then(r => r.json())
        .then(data => {
            if (data.success) renderBankList(data.questions);
            else bankList.innerHTML = '<div class="alert alert-danger text-center">فشل تحميل البنك.</div>';
        })
        .catch(() => bankList.innerHTML = '<div class="alert alert-danger text-center">خطأ في الاتصال.</div>');
    }

    function renderBankList(questions) {
        if (!questions.length) {
            bankList.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-info-circle fa-2x mb-2 d-block opacity-25"></i>لا توجد أسئلة مطابقة.</div>';
            return;
        }
        bankList.innerHTML = questions.map(q => {
            let opts = '';
            if (q.type === 'mcq' && q.options) {
                opts = '<ul class="list-group list-group-flush border rounded my-2 bg-white small">';
                q.options.forEach(o => { opts += `<li class="list-group-item py-1 ${o.is_correct ? 'list-group-item-success fw-bold' : ''}">${o.is_correct ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="far fa-circle me-1"></i>'}${o.option_text}</li>`; });
                opts += '</ul>';
            } else if (q.type === 'true_false' && q.options?.[0]) {
                opts = `<div class="my-1 small text-success fw-bold"><i class="fas fa-check-circle me-1"></i>الإجابة: ${q.options[0].is_correct ? 'صح' : 'خطأ'}</div>`;
            }
            return `
            <div class="card mb-2 border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-secondary small">${q.type}</span>
                            <span class="badge bg-success small">${parseFloat(q.mark)} درجة</span>
                            <span class="badge bg-light text-dark border small">${q.difficulty}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary flex-shrink-0 import-to-exam-btn" data-id="${q.id}" data-mark="${q.mark}">
                            <i class="fas fa-plus-circle me-1"></i>استيراد
                        </button>
                    </div>
                    <p class="mb-1 mt-2 text-dark fw-bold small">${q.question_text}</p>
                    ${opts}
                </div>
            </div>`;
        }).join('');

        bankList.querySelectorAll('.import-to-exam-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                this.disabled = true;
                fetch(IMPORT_URL, {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question_id: this.dataset.id, mark_override: this.dataset.mark })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else { alert(data.message || 'فشل الاستيراد'); this.disabled = false; }
                })
                .catch(() => this.disabled = false);
            });
        });
    }

    // ── 8. Search in Questions ────────────────────────────────────────────────
    const searchInput = document.getElementById('searchExamQuestions');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const val = this.value.toLowerCase();
            questionsContainer.querySelectorAll('.question-item').forEach(card => {
                const text = card.querySelector('.question-text-content')?.textContent.toLowerCase() || '';
                card.style.setProperty('display', text.includes(val) ? 'block' : 'none', 'important');
            });
        });
    }

    function debounce(fn, wait) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
    }
});
</script>
@endpush
