@extends('layouts.app')

@section('title', 'تصميم الأسئلة')

@section('content')
@include('components.page-header', [
    'title' => 'تصميم الأسئلة: ' . $exam->title,
    'subtitle' => $exam->subject->name . ' | ' . $exam->schoolClass->name . ' (' . $exam->section->name . ')'
])

<div class="row">
    <!-- Left Column: Exam Details & Summary -->
    <div class="col-md-4">
        @include('panels.admin.exams.components.exam-summary', ['exam' => $exam])

        @if($exam->status === \App\Enums\ExamStatus::DRAFT)
        <!-- Question Creator/Editor Card -->
        <div class="card shadow-sm border-0 border-top border-primary border-3 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title fw-bold mb-0" id="creatorCardTitle"><i class="fas fa-plus-circle"></i> إضافة سؤال جديد</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="cancelEditBtn">إلغاء التعديل</button>
                </div>
                <form id="questionForm">
                    @csrf
                    <!-- Hidden field for editing questions -->
                    <input type="hidden" name="question_id" id="editQuestionId" value="">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">نوع السؤال</label>
                        <select class="form-select form-select-sm" name="type" id="questionType" required>
                            <option value="">اختر النوع...</option>
                            <option value="mcq">اختيار من متعدد (MCQ)</option>
                            <option value="true_false">صح / خطأ</option>
                            <option value="short_answer">إجابة قصيرة</option>
                            <option value="essay">مقال</option>
                            <option value="matching">توصيل / مطابقة</option>
                            <option value="fill_blank">إكمال الفراغ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">نص السؤال</label>
                        <textarea class="form-control" name="question_text" id="questionText" rows="3" required placeholder="اكتب نص السؤال هنا..."></textarea>
                    </div>

                    <div class="row mb-3 g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">الدرجة</label>
                            <input type="number" step="0.5" min="0.5" class="form-control form-control-sm" name="mark" id="questionMark" value="1.0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">الصعوبة</label>
                            <select class="form-select form-select-sm" name="difficulty" id="questionDifficulty">
                                <option value="easy">سهل (Easy)</option>
                                <option value="medium" selected>متوسط (Medium)</option>
                                <option value="hard">صعب (Hard)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3 g-2">
                        <div class="col-12">
                            <label class="form-label small fw-bold">الوقت المقدر (ثواني)</label>
                            <input type="number" min="5" class="form-control form-control-sm" name="estimated_time" id="questionEstimatedTime" placeholder="مثال: 60">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="questionIsPublic" value="1" checked>
                        <label class="form-check-label small" for="questionIsPublic">إتاحة السؤال في بنك الأسئلة العام</label>
                    </div>

                    <!-- Dynamic Fields Container -->
                    <div id="dynamicFields" class="p-3 bg-light rounded mb-3 d-none">
                        <!-- Content injected via JS -->
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="saveQuestionBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="saveBtnText">حفظ السؤال</span>
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column: Exam Question List & Toolbar -->
    <div class="col-md-8">
        <!-- Search & Actions Toolbar -->
        @include('panels.admin.exams.components.toolbar', ['exam' => $exam])

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-list"></i> أسئلة الامتحان الحالية</h5>
            </div>
            <div class="card-body bg-light" id="questionsContainer" style="min-height: 500px;">
                @forelse($exam->questions as $index => $q)
                    @include('panels.admin.exams.components.question-card', ['q' => $q, 'index' => $index])
                @empty
                    <div class="text-center py-5 text-muted" id="noQuestionsMsg">
                        <i class="fas fa-inbox fa-3x d-block mb-3 text-muted"></i>
                        <h5>لا توجد أسئلة مضافة بعد.</h5>
                        <p class="small">استخدم محرر الأسئلة على اليمين، أو استورد أسئلة جاهزة من بنك الأسئلة.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Form Templates for Question Types -->
@include('panels.admin.exams.components.forms.mcq-form')
@include('panels.admin.exams.components.forms.truefalse-form')
@include('panels.admin.exams.components.forms.matching-form')
@include('panels.admin.exams.components.forms.essay-form')
@include('panels.admin.exams.components.forms.fillblank-form')

<!-- Question Bank Import Modal -->
@include('panels.admin.exams.components.question-bank-modal', ['exam' => $exam])

@endsection

@push('styles')
<style>
    .cursor-move {
        cursor: move !important;
    }
    .question-item {
        transition: all 0.2s ease;
    }
    .question-item.sortable-ghost {
        opacity: 0.4;
        background-color: #e9ecef !important;
        border: 2px dashed #007bff !important;
    }
</style>
@endpush

@push('scripts')
<!-- SortableJS library for Drag & Drop support -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('questionType');
    const dynamicFields = document.getElementById('dynamicFields');
    const form = document.getElementById('questionForm');
    const saveBtn = document.getElementById('saveQuestionBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const creatorCardTitle = document.getElementById('creatorCardTitle');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editQuestionId = document.getElementById('editQuestionId');
    const questionsContainer = document.getElementById('questionsContainer');
    const noQuestionsMsg = document.getElementById('noQuestionsMsg');
    const totalMarksDisplay = document.getElementById('totalMarksDisplay');
    const questionCountDisplay = document.getElementById('questionCount');

    // CSRF Token header config
    const headers = {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    };

    // 1. Dynamic Forms Switching
    typeSelect.addEventListener('change', function() {
        renderFormForType(this.value);
    });

    function renderFormForType(type, optionsData = null) {
        dynamicFields.innerHTML = '';
        if (!type) {
            dynamicFields.classList.add('d-none');
            return;
        }
        dynamicFields.classList.remove('d-none');

        if (type === 'mcq') {
            const template = document.getElementById('mcq-form-template').innerHTML;
            dynamicFields.innerHTML = template;
            setupMcqForm(optionsData);
        } else if (type === 'true_false') {
            const template = document.getElementById('truefalse-form-template').innerHTML;
            dynamicFields.innerHTML = template;
            if (optionsData && optionsData.length > 0) {
                const correct = optionsData[0].is_correct;
                document.getElementById(correct ? 'tfTrue' : 'tfFalse').checked = true;
            }
        } else if (type === 'matching') {
            const template = document.getElementById('matching-form-template').innerHTML;
            dynamicFields.innerHTML = template;
            setupMatchingForm(optionsData);
        } else if (type === 'essay') {
            const template = document.getElementById('essay-form-template').innerHTML;
            dynamicFields.innerHTML = template;
        } else if (type === 'fill_blank') {
            const template = document.getElementById('fillblank-form-template').innerHTML;
            dynamicFields.innerHTML = template;
        } else {
            dynamicFields.classList.add('d-none');
        }
    }

    // MCQ setup helper
    function setupMcqForm(optionsData = null) {
        const container = document.getElementById('mcqContainer');
        const addBtn = document.getElementById('addMcqOptionBtn');

        if (optionsData && optionsData.length > 0) {
            optionsData.forEach((opt, idx) => {
                addMcqOptionRow(container, idx, opt.option_text, opt.is_correct);
            });
        } else {
            addMcqOptionRow(container, 0, '', true);
            addMcqOptionRow(container, 1, '', false);
        }

        addBtn.addEventListener('click', function() {
            const count = container.children.length;
            addMcqOptionRow(container, count, '', false);
        });
    }

    function addMcqOptionRow(container, index, val = '', checked = false) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 mcq-option-row';
        div.innerHTML = `
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" name="correct_option_index" value="${index}" required ${checked ? 'checked' : ''}>
            </div>
            <input type="text" class="form-control form-control-sm" name="options[]" value="${val}" placeholder="الخيار ${index + 1}" required>
            ${index > 1 ? '<button class="btn btn-sm btn-outline-danger remove-btn" type="button"><i class="fas fa-times"></i></button>' : ''}
        `;
        container.appendChild(div);
        const removeBtn = div.querySelector('.remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                div.remove();
                reindexRadios(container, 'correct_option_index');
            });
        }
    }

    // Matching setup helper
    function setupMatchingForm(optionsData = null) {
        const container = document.getElementById('matchingContainer');
        const addBtn = document.getElementById('addMatchPairBtn');

        if (optionsData && optionsData.length > 0) {
            optionsData.forEach((opt, idx) => {
                addMatchingRow(container, idx, opt.left_item, opt.right_item, opt.partial_mark);
            });
        } else {
            addMatchingRow(container, 0, '', '', '');
            addMatchingRow(container, 1, '', '', '');
        }

        addBtn.addEventListener('click', function() {
            const count = container.querySelectorAll('.match-row').length;
            addMatchingRow(container, count, '', '', '');
        });
    }

    function addMatchingRow(container, index, left = '', right = '', partial = '') {
        const div = document.createElement('div');
        div.className = 'row mb-2 match-row align-items-center g-1';
        div.innerHTML = `
            <div class="col-4">
                <input type="text" class="form-control form-control-sm" name="pairs[${index}][left]" value="${left}" placeholder="العنصر الأيمن" required>
            </div>
            <div class="col-1 text-center"><i class="fas fa-arrow-right text-muted small"></i></div>
            <div class="col-4">
                <input type="text" class="form-control form-control-sm" name="pairs[${index}][right]" value="${right}" placeholder="العنصر الأيسر" required>
            </div>
            <div class="col-2">
                <input type="number" step="0.25" min="0" class="form-control form-control-sm" name="pairs[${index}][partial_mark]" value="${partial}" placeholder="درجة جزئية">
            </div>
            <div class="col-1 text-end">
                ${index > 1 ? '<button class="btn btn-sm btn-outline-danger remove-btn w-100" type="button"><i class="fas fa-times"></i></button>' : ''}
            </div>
        `;
        container.appendChild(div);
        const removeBtn = div.querySelector('.remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                div.remove();
                reindexMatchingRows(container);
            });
        }
    }

    function reindexRadios(container, name) {
        container.querySelectorAll('input[type="radio"]').forEach((radio, idx) => {
            radio.value = idx;
        });
    }

    function reindexMatchingRows(container) {
        container.querySelectorAll('.match-row').forEach((row, idx) => {
            row.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name.includes('left')) input.setAttribute('name', `pairs[${idx}][left]`);
                if (name.includes('right')) input.setAttribute('name', `pairs[${idx}][right]`);
                if (name.includes('partial_mark')) input.setAttribute('name', `pairs[${idx}][partial_mark]`);
            });
        });
    }

    // 2. Drag & Drop (SortableJS)
    if (questionsContainer) {
        new Sortable(questionsContainer, {
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            animation: 150,
            onEnd: function() {
                const orderedIds = Array.from(questionsContainer.querySelectorAll('.question-item')).map(el => el.dataset.id);
                
                fetch(`{{ route('admin.questions.reorder', $exam->id) }}`, {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ordered_ids: orderedIds })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Re-index displayed badges
                        questionsContainer.querySelectorAll('.question-item').forEach((card, idx) => {
                            const badge = card.querySelector('.badge.bg-secondary');
                            if (badge) badge.textContent = `سؤال ${idx + 1}`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    // 3. Save / Update Question Form Submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const spinner = saveBtn.querySelector('.spinner-border');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        const qId = editQuestionId.value;
        const isEdit = !!qId;
        const url = isEdit 
            ? `{{ url('admin/exams/'.$exam->id.'/questions') }}/${qId}`
            : `{{ route('admin.questions.store', $exam->id) }}`;
        
        const method = isEdit ? 'PUT' : 'POST';
        const formData = new FormData(form);

        // Convert FormData to standard request for PUT if necessary
        let bodyPayload;
        let requestHeaders = { ...headers };
        
        if (isEdit) {
            // Laravel PUT support for FormData can be tricky, convert to JSON
            const object = {};
            formData.forEach((value, key) => {
                if (key.endsWith('[]')) {
                    const actualKey = key.slice(0, -2);
                    if (!object[actualKey]) object[actualKey] = [];
                    object[actualKey].push(value);
                } else if (key.startsWith('pairs[')) {
                    // Extract match pairs
                    const matches = key.match(/pairs\[(\d+)\]\[(\w+)\]/);
                    if (matches) {
                        const idx = matches[1];
                        const field = matches[2];
                        if (!object['pairs']) object['pairs'] = [];
                        if (!object['pairs'][idx]) object['pairs'][idx] = {};
                        object['pairs'][idx][field] = value;
                    }
                } else {
                    object[key] = value;
                }
            });
            bodyPayload = JSON.stringify(object);
            requestHeaders['Content-Type'] = 'application/json';
        } else {
            bodyPayload = formData;
        }

        fetch(url, {
            method: method,
            headers: requestHeaders,
            body: bodyPayload
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'خطأ أثناء حفظ السؤال');
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        })
        .catch(err => {
            console.error(err);
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // 4. Edit Question Mode Trigger
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-question');
        if (!btn) return;

        const id = btn.dataset.id;
        const type = btn.dataset.type;
        const text = btn.dataset.text;
        const mark = btn.dataset.mark;
        const difficulty = btn.dataset.difficulty;
        const time = btn.dataset.estimatedTime;
        const isPublic = btn.dataset.public === '1';
        const options = JSON.parse(btn.dataset.options);

        // Switch UI to Edit Mode
        editQuestionId.value = id;
        typeSelect.value = type;
        typeSelect.disabled = true; // Block changing type on edit for safety, or leave enabled since Service handles it. We'll disable it for simplicity.
        document.getElementById('questionText').value = text;
        document.getElementById('questionMark').value = mark;
        document.getElementById('questionDifficulty').value = difficulty;
        document.getElementById('questionEstimatedTime').value = time;
        document.getElementById('questionIsPublic').checked = isPublic;

        renderFormForType(type, options);

        creatorCardTitle.innerHTML = `<i class="fas fa-edit text-warning"></i> تعديل السؤال`;
        saveBtnText.textContent = 'تحديث السؤال';
        saveBtn.className = 'btn btn-warning w-100';
        cancelEditBtn.classList.remove('d-none');
    });

    // Cancel Edit Trigger
    cancelEditBtn.addEventListener('click', resetForm);

    function resetForm() {
        form.reset();
        editQuestionId.value = '';
        typeSelect.disabled = false;
        typeSelect.value = '';
        dynamicFields.innerHTML = '';
        dynamicFields.classList.add('d-none');

        creatorCardTitle.innerHTML = `<i class="fas fa-plus-circle"></i> إضافة سؤال جديد`;
        saveBtnText.textContent = 'حفظ السؤال';
        saveBtn.className = 'btn btn-primary w-100';
        cancelEditBtn.classList.add('d-none');
    }

    // 5. Duplicate Question
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.duplicate-question');
        if (!btn) return;

        const id = btn.dataset.id;
        btn.disabled = true;

        fetch(`{{ url('admin/exams/'.$exam->id.'/questions') }}/${id}/duplicate`, {
            method: 'POST',
            headers: headers
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'فشل في نسخ السؤال');
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
        });
    });

    // 6. Delete/Remove Question
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.delete-question');
        if (!btn) return;

        if (!confirm('هل أنت متأكد من رغبتك في حذف هذا السؤال من الامتحان؟')) return;

        const id = btn.dataset.id;
        btn.disabled = true;

        fetch(`{{ url('admin/exams/'.$exam->id.'/questions') }}/${id}`, {
            method: 'DELETE',
            headers: headers
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'فشل في حذف السؤال');
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
        });
    });

    // 7. Question Bank Import Modal Handling
    const bankModal = document.getElementById('questionBankModal');
    const bankQuestionsList = document.getElementById('bankQuestionsList');
    const bankSearch = document.getElementById('bankSearch');
    const bankTypeFilter = document.getElementById('bankTypeFilter');
    const bankDifficultyFilter = document.getElementById('bankDifficultyFilter');
    const refreshBankBtn = document.getElementById('refreshBankBtn');

    if (bankModal) {
        bankModal.addEventListener('show.bs.modal', loadBankQuestions);
        bankSearch.addEventListener('input', debounce(loadBankQuestions, 400));
        bankTypeFilter.addEventListener('change', loadBankQuestions);
        bankDifficultyFilter.addEventListener('change', loadBankQuestions);
        refreshBankBtn.addEventListener('click', loadBankQuestions);
    }

    function loadBankQuestions() {
        bankQuestionsList.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                جاري تحميل الأسئلة من البنك...
            </div>
        `;

        const queryParams = new URLSearchParams({
            search: bankSearch.value,
            type: bankTypeFilter.value,
            difficulty: bankDifficultyFilter.value
        });

        fetch(`{{ route('admin.questions.bank', $exam->id) }}?${queryParams.toString()}`, {
            method: 'GET',
            headers: headers
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderBankList(data.questions);
            } else {
                bankQuestionsList.innerHTML = `<div class="alert alert-danger text-center">فشل تحميل بنك الأسئلة.</div>`;
            }
        })
        .catch(err => {
            console.error(err);
            bankQuestionsList.innerHTML = `<div class="alert alert-danger text-center">خطأ أثناء معالجة الطلب.</div>`;
        });
    }

    function renderBankList(questions) {
        if (questions.length === 0) {
            bankQuestionsList.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                    لا توجد أسئلة متوفرة في البنك تطابق البحث.
                </div>
            `;
            return;
        }

        let html = '';
        questions.forEach(q => {
            let optionsHtml = '';
            if (q.type === 'mcq' && q.options) {
                optionsHtml = '<ul class="list-group list-group-flush border rounded my-2 bg-white small">';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <li class="list-group-item py-1 ${opt.is_correct ? 'list-group-item-success fw-bold text-success' : ''}">
                            ${opt.is_correct ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="far fa-circle me-1"></i>'}
                            ${opt.option_text}
                        </li>
                    `;
                });
                optionsHtml += '</ul>';
            } else if (q.type === 'true_false' && q.options && q.options[0]) {
                optionsHtml = `
                    <div class="my-2 p-1 bg-white border rounded small text-success fw-bold">
                        إجابة صحيحة: ${q.options[0].is_correct ? 'True (صح)' : 'False (خطأ)'}
                    </div>
                `;
            } else if (q.type === 'matching' && q.options) {
                optionsHtml = '<div class="row g-1 my-2">';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <div class="col-6">
                            <span class="badge bg-white border text-dark p-1 w-100 text-start text-truncate">
                                ${opt.left_item} <i class="fas fa-arrow-right mx-1 text-muted"></i> ${opt.right_item}
                            </span>
                        </div>
                    `;
                });
                optionsHtml += '</div>';
            }

            html += `
                <div class="card mb-2 border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="badge bg-secondary text-uppercase small">${q.type}</span>
                                <span class="badge bg-success small">${parseFloat(q.mark)} درجات</span>
                                <span class="badge bg-light text-dark border small">${q.difficulty}</span>
                                <span class="badge bg-light text-muted border small">${q.question_code}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary import-to-exam-btn" data-id="${q.id}" data-mark="${q.mark}">
                                <i class="fas fa-plus-circle"></i> استيراد
                            </button>
                        </div>
                        <p class="mb-1 mt-2 text-dark fw-bold small">${q.question_text}</p>
                        ${optionsHtml}
                    </div>
                </div>
            `;
        });

        bankQuestionsList.innerHTML = html;

        // Setup import listener
        bankQuestionsList.querySelectorAll('.import-to-exam-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const defaultMark = this.dataset.mark;
                this.disabled = true;

                fetch(`{{ route('admin.questions.import', $exam->id) }}`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ question_id: id, mark_override: defaultMark })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'فشل استيراد السؤال');
                        this.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.disabled = false;
                });
            });
        });
    }

    // Debounce helper
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // 8. Local Questions Filter Search
    const searchExamInput = document.getElementById('searchExamQuestions');
    if (searchExamInput) {
        searchExamInput.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            document.querySelectorAll('#questionsContainer .question-item').forEach(card => {
                const text = card.querySelector('.question-text-content').textContent.toLowerCase();
                const type = card.querySelector('.badge.bg-info').textContent.toLowerCase();
                if (text.includes(val) || type.includes(val)) {
                    card.style.setProperty('display', 'block', 'important');
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

    // 9. Auto-save local draft feature on field blurs (for questionText/Mark)
    // We will auto-save the active question creator form to localStorage on changes
    const inputsToTrack = ['questionText', 'questionMark', 'questionDifficulty', 'questionEstimatedTime', 'questionType'];
    inputsToTrack.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', saveFormDraft);
            el.addEventListener('blur', saveFormDraft);
        }
    });

    function saveFormDraft() {
        if (editQuestionId.value) return; // Don't auto-save active edits to general draft
        const draft = {
            text: document.getElementById('questionText').value,
            type: typeSelect.value,
            mark: document.getElementById('questionMark').value,
            difficulty: document.getElementById('questionDifficulty').value,
            time: document.getElementById('questionEstimatedTime').value,
        };
        localStorage.setItem(`exam_${{$exam->id}}_question_draft`, JSON.stringify(draft));
    }

    // Load draft on load
    const savedDraftStr = localStorage.getItem(`exam_${{$exam->id}}_question_draft`);
    if (savedDraftStr && !editQuestionId.value) {
        try {
            const draft = JSON.parse(savedDraftStr);
            if (draft.type) {
                typeSelect.value = draft.type;
                renderFormForType(draft.type);
            }
            if (draft.text) document.getElementById('questionText').value = draft.text;
            if (draft.mark) document.getElementById('questionMark').value = draft.mark;
            if (draft.difficulty) document.getElementById('questionDifficulty').value = draft.difficulty;
            if (draft.time) document.getElementById('questionEstimatedTime').value = draft.time;
        } catch (e) {
            console.error('Error loading draft', e);
        }
    }

    // Clear draft on successful submit
    form.addEventListener('submit', function() {
        localStorage.removeItem(`exam_${{$exam->id}}_question_draft`);
    });
});
</script>
@endpush
