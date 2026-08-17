@extends('layouts.app')
@section('title', 'تقديم الاختبار — ' . $exam->title)

@push('styles')
<style>
    /* ── Exam Security ── */
    body { -webkit-user-select: none; user-select: none; }
    @media print { body { visibility: hidden !important; } }

    /* ── Start Screen Overlay ── */
    #examStartScreen {
        position: fixed; inset: 0; z-index: 1050;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .start-card {
        background: #fff; border-radius: 20px; padding: 2.5rem;
        max-width: 600px; width: 100%; box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        text-align: center;
    }
    .start-icon-wrap {
        width: 90px; height: 90px; border-radius: 50%; margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        display: flex; align-items: center; justify-content: center;
    }
    .start-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin: 1.5rem 0; }
    .start-stat {
        background: #f8fafc; border-radius: 12px; padding: 1rem .5rem;
        border: 1px solid #e2e8f0;
    }
    .start-stat .val { font-size: 1.4rem; font-weight: 700; color: #1e293b; }
    .start-stat .lbl { font-size: .75rem; color: #64748b; margin-top: .2rem; }
    .instructions-box {
        background: #fffbeb; border: 1px solid #fcd34d;
        border-radius: 12px; padding: 1rem 1.25rem;
        text-align: right; font-size: .875rem; margin-bottom: 1.5rem;
    }
    .instructions-box ul { margin: 0; padding-right: 1.25rem; }
    .btn-start-exam {
        background: linear-gradient(135deg, #3b82f6, #6366f1); color: #fff;
        border: none; padding: .85rem 3rem; border-radius: 50px;
        font-size: 1.1rem; font-weight: 600; cursor: pointer;
        transition: transform .2s, box-shadow .2s;
    }
    .btn-start-exam:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,.4); }

    /* ── Floating Timer ── */
    #floatingTimer {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        z-index: 999; background: #1e293b; color: #fff;
        padding: .5rem 1.5rem; border-radius: 50px;
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.1rem; font-weight: 700;
        box-shadow: 0 8px 30px rgba(0,0,0,.3);
        transition: background .3s;
        pointer-events: none;
    }
    #floatingTimer.danger { background: #dc2626; animation: pulse 1s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.7} }

    /* ── Progress Bar ── */
    .exam-progress-bar { background: #f1f5f9; border-radius: 16px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .progress-info { display: flex; justify-content: space-between; margin-bottom: .5rem; font-size: .85rem; color: #64748b; }
    .progress-track { height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #3b82f6, #6366f1); border-radius: 99px; transition: width .4s; }

    /* ── Question Card ── */
    .question-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        padding: 1.75rem; margin-bottom: 1.25rem;
        border: 2px solid transparent; transition: border .2s;
    }
    .question-card.answered { border-color: #22c55e20; }
    .question-number-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: #fff; font-weight: 700; font-size: .9rem;
        flex-shrink: 0; margin-left: .75rem;
    }

    /* ── Answer Options (MCQ / True-False) ── */
    .answer-option-label {
        display: flex; align-items: center; gap: .75rem;
        border: 2px solid #e2e8f0; border-radius: 12px;
        padding: .85rem 1rem; cursor: pointer;
        transition: all .2s; margin-bottom: .6rem;
        background: #fff; user-select: none;
    }
    .answer-option-label:hover { border-color: #93c5fd; background: #eff6ff; }
    .answer-option-label input[type="radio"] { display: none; }
    .answer-option-label input[type="radio"]:checked ~ .opt-content { color: #1d4ed8; font-weight: 600; }
    .answer-option-label:has(input:checked) {
        border-color: #3b82f6; background: #eff6ff;
    }
    .opt-letter {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
        border: 2px solid #cbd5e1; background: #f8fafc;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .85rem; color: #475569;
        transition: all .2s;
    }
    .answer-option-label:has(input:checked) .opt-letter {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    /* True/False special */
    .tf-option { flex: 1; }
    .tf-option .answer-option-label { justify-content: center; font-size: 1rem; font-weight: 600; }
    .tf-option.true-opt .answer-option-label:has(input:checked) { border-color: #22c55e; background: #f0fdf4; }
    .tf-option.true-opt .answer-option-label:has(input:checked) .opt-letter { background: #22c55e; border-color: #22c55e; }
    .tf-option.false-opt .answer-option-label:has(input:checked) { border-color: #ef4444; background: #fef2f2; }
    .tf-option.false-opt .answer-option-label:has(input:checked) .opt-letter { background: #ef4444; border-color: #ef4444; }

    /* ── Navigator Sidebar ── */
    .q-nav-grid { display: flex; flex-wrap: wrap; gap: .4rem; }
    .q-nav-btn {
        width: 38px; height: 38px; border-radius: 8px; border: 2px solid #e2e8f0;
        background: #fff; font-weight: 600; font-size: .85rem; cursor: pointer;
        transition: all .2s; color: #475569;
    }
    .q-nav-btn:hover { border-color: #93c5fd; background: #eff6ff; }
    .q-nav-btn.current { border-color: #3b82f6; background: #3b82f6; color: #fff; }
    .q-nav-btn.answered { border-color: #22c55e; background: #f0fdf4; color: #15803d; }
    .q-nav-legend { display: flex; gap: 1rem; font-size: .75rem; color: #64748b; margin-top: .75rem; flex-wrap: wrap; }
    .leg-dot { width: 12px; height: 12px; border-radius: 3px; display: inline-block; margin-left: .3rem; }

    /* ── Navigation Buttons ── */
    .exam-nav-btns { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; gap: .75rem; }

    /* ── Security overlay ── */
    #securityOverlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.88); z-index: 9999;
        display: none; flex-direction: column; align-items: center; justify-content: center;
        color: white; text-align: center; padding: 20px;
    }
    .blur-content { filter: blur(15px); pointer-events: none; }
</style>
@endpush

@section('content')

{{-- ══════════════════ START SCREEN OVERLAY ══════════════════ --}}
<div id="examStartScreen">
    <div class="start-card">
        <div class="start-icon-wrap">
            <i class="fas fa-graduation-cap fa-2x text-white"></i>
        </div>
        <h4 class="fw-bold mb-1">أنت على وشك بدء اختبار</h4>
        <h5 class="text-primary mb-1">{{ $exam->title }}</h5>
        <p class="text-muted small">{{ $exam->subject?->name }}</p>

        <div class="start-stats">
            <div class="start-stat">
                <div class="val">{{ $exam->duration_minutes ?? '—' }}</div>
                <div class="lbl">دقيقة</div>
            </div>
            <div class="start-stat">
                <div class="val">{{ $exam->questions->count() }}</div>
                <div class="lbl">سؤال</div>
            </div>
            <div class="start-stat">
                <div class="val">{{ $exam->total_marks }}</div>
                <div class="lbl">درجة كلية</div>
            </div>
        </div>

        <div class="instructions-box">
            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1 text-warning"></i> تعليمات هامة:</h6>
            <ul>
                <li>بمجرد الضغط على "بدء الاختبار"، سيبدأ المؤقت ولا يمكن إيقافه.</li>
                <li>الخروج من الصفحة أو إغلاق التبويب قد يؤدي إلى تقديم الاختبار تلقائياً.</li>
                @if($exam->instructions)
                    <li>{{ $exam->instructions }}</li>
                @endif
                <li>تأكد من استقرار اتصال الإنترنت قبل البدء.</li>
                <li class="text-success fw-bold"><i class="fas fa-cloud-upload-alt me-1"></i> إجاباتك تُحفظ تلقائياً على السيرفر كل 15 ثانية.</li>
            </ul>
        </div>

        <button class="btn-start-exam" id="btnStartExam">
            <i class="fas fa-play-circle me-2"></i> بدء الاختبار الآن
        </button>
        <a href="{{ route('student.exams') }}" class="btn btn-outline-secondary mt-3 d-block">العودة للاختبارات</a>
    </div>
</div>

{{-- ══════════════════ FLOATING TIMER ══════════════════ --}}
<div id="floatingTimer" style="display:none;">
    <i class="fas fa-clock"></i>
    <span id="floatingTimerDisplay">--:--</span>
</div>

{{-- ══════════════════ SECURITY OVERLAY ══════════════════ --}}
<div id="securityOverlay">
    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
    <h3>انتبه!</h3>
    <p>تم إخفاء محتوى الاختبار لأنك غادرت الصفحة.<br>يُرجى العودة للتركيز على الاختبار.</p>
</div>

{{-- ══════════════════ EXAM BODY ══════════════════ --}}
<div class="row g-4" id="examBody">
    <div class="col-xl-8">

        {{-- Progress bar --}}
        <div class="exam-progress-bar">
            <div class="progress-info">
                <span>السؤال <strong id="currentQNum">1</strong> من <strong>{{ $exam->questions->count() }}</strong></span>
                <span id="answeredCountText">0 / {{ $exam->questions->count() }} تمت الإجابة</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" id="progressFill" style="width: {{ $exam->questions->count() > 0 ? round(100/$exam->questions->count()) : 0 }}%"></div>
            </div>
        </div>

        {{-- Exam form --}}
        <form id="exam-form" action="{{ route('student.exams.submit', $exam) }}" method="POST">
            @csrf

            @foreach($exam->questions as $index => $question)
            @php
                $letters = ['أ','ب','ج','د','هـ','و'];
                $qType   = $question->type->value;
            @endphp
            <div class="question-card" id="qcard-{{ $index }}" data-index="{{ $index }}"
                 style="display: {{ $index === 0 ? 'block' : 'none' }}">

                <div class="d-flex align-items-start mb-3">
                    <span class="question-number-badge">{{ $index + 1 }}</span>
                    <div class="flex-grow-1">
                        <span class="fw-bold" style="font-size:1.05rem">{{ $question->question_text }}</span>
                        <span class="badge bg-secondary ms-2" style="font-size:.75rem">{{ $question->marks }} درجة</span>
                    </div>
                </div>

                {{-- MCQ --}}
                @if($qType === 'mcq')
                    @foreach($question->options as $optIdx => $option)
                    <label class="answer-option-label w-100" for="opt_{{ $option->id }}">
                        <input type="radio" id="opt_{{ $option->id }}" name="answers[{{ $question->id }}]"
                               value="{{ $option->id }}" class="student-answer" data-qid="{{ $question->id }}" data-qindex="{{ $index }}">
                        <span class="opt-letter">{{ $letters[$optIdx] ?? $optIdx+1 }}</span>
                        <span class="opt-content">{{ $option->option_text }}</span>
                    </label>
                    @endforeach

                {{-- TRUE / FALSE --}}
                @elseif($qType === 'true_false')
                    <div class="d-flex gap-3">
                        @foreach($question->options as $option)
                        <div class="tf-option {{ $option->option_text === 'صح' ? 'true-opt' : 'false-opt' }}">
                            <label class="answer-option-label w-100" for="opt_{{ $option->id }}">
                                <input type="radio" id="opt_{{ $option->id }}" name="answers[{{ $question->id }}]"
                                       value="{{ $option->id }}" class="student-answer" data-qid="{{ $question->id }}" data-qindex="{{ $index }}">
                                <span class="opt-letter">
                                    @if($option->option_text === 'صح') <i class="fas fa-check"></i>
                                    @else <i class="fas fa-times"></i>
                                    @endif
                                </span>
                                <span class="opt-content">{{ $option->option_text }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>

                {{-- SHORT ANSWER / FILL BLANK --}}
                @elseif($qType === 'short_answer' || $qType === 'fill_blank')
                    <input type="text" name="answers[{{ $question->id }}]"
                           class="form-control form-control-lg student-answer"
                           placeholder="اكتب إجابتك هنا..."
                           data-qid="{{ $question->id }}" data-qindex="{{ $index }}" autocomplete="off">

                {{-- ESSAY --}}
                @elseif($qType === 'essay')
                    <textarea name="answers[{{ $question->id }}]"
                              class="form-control student-answer" rows="5"
                              placeholder="اكتب إجابتك المقالية هنا..."
                              data-qid="{{ $question->id }}" data-qindex="{{ $index }}"></textarea>

                {{-- MATCHING --}}
                @elseif($qType === 'matching')
                    @php $rightItems = $question->options->pluck('right_item')->shuffle(); @endphp
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                @foreach($question->options as $option)
                                <tr>
                                    <td class="w-50 align-middle fw-bold bg-light">{{ $option->left_item }}</td>
                                    <td class="w-50">
                                        <select name="answers[{{ $question->id }}][{{ $option->id }}]"
                                                class="form-select student-answer"
                                                data-qid="{{ $question->id }}_{{ $option->id }}" data-qindex="{{ $index }}">
                                            <option value="">-- اختر الإجابة --</option>
                                            @foreach($rightItems as $rightItem)
                                            <option value="{{ $rightItem }}">{{ $rightItem }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Navigation Buttons --}}
                <div class="exam-nav-btns">
                    <button type="button" class="btn btn-outline-secondary px-4"
                            onclick="goToQuestion({{ $index - 1 }})"
                            {{ $index === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-arrow-right me-1"></i> السابق
                    </button>

                    @if($index === $exam->questions->count() - 1)
                        <button type="button" class="btn btn-success px-4 fw-bold"
                                onclick="confirmSubmit()">
                            <i class="fas fa-paper-plane me-1"></i> تسليم الاختبار
                        </button>
                    @else
                        <button type="button" class="btn btn-primary px-4"
                                onclick="goToQuestion({{ $index + 1 }})">
                            التالي <i class="fas fa-arrow-left ms-1"></i>
                        </button>
                    @endif
                </div>
            </div>
            @endforeach

        </form>
    </div>

    {{-- Sidebar --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
            <div class="card-body">
                <h6 class="fw-bold mb-1"><i class="fas fa-clock text-primary me-1"></i> الوقت المتبقي</h6>
                <div class="display-6 fw-bold text-primary text-center my-2" id="timer-display">--:--</div>

                {{-- Auto-Save Indicator --}}
                <div id="autoSaveIndicator" class="text-center mb-2" style="min-height:22px;font-size:.82rem;"></div>

                <hr>

                <h6 class="fw-bold mb-2"><i class="fas fa-th text-info me-1"></i> تنقل الأسئلة</h6>
                <p class="text-muted small mb-2">اضغط على رقم السؤال للانتقال إليه</p>
                <div class="q-nav-grid" id="questionNavigator">
                    @foreach($exam->questions as $index => $q)
                    <button class="q-nav-btn {{ $index === 0 ? 'current' : '' }}"
                            id="navbtn-{{ $index }}"
                            onclick="goToQuestion({{ $index }})" type="button">
                        {{ $index + 1 }}
                    </button>
                    @endforeach
                </div>
                <div class="q-nav-legend">
                    <span><span class="leg-dot" style="background:#3b82f6"></span> الحالي</span>
                    <span><span class="leg-dot" style="background:#f0fdf4;border:1px solid #22c55e"></span> أُجيب عنه</span>
                    <span><span class="leg-dot" style="background:#fff;border:1px solid #e2e8f0"></span> لم يُجب</span>
                </div>

                <hr>
                <div class="small text-muted">
                    <p class="mb-1"><i class="fas fa-book me-1"></i> <strong>المادة:</strong> {{ $exam->subject?->name }}</p>
                    <p class="mb-0"><i class="fas fa-star me-1"></i> <strong>الدرجة الكلية:</strong> {{ $exam->total_marks }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalQuestions   = {{ $exam->questions->count() }};
    const examId           = {{ $exam->id }};
    const storageKey       = `exam_${examId}_answers`;
    const examDuration     = {{ isset($remainingSeconds) ? $remainingSeconds : ($exam->duration_minutes ?? 60) * 60 }};
    const autoSaveUrl      = '{{ route('student.exams.auto-save', $exam->id) }}';
    const csrfToken        = '{{ csrf_token() }}';

    let currentIndex   = 0;
    let answeredSet    = new Set();
    let timerInterval  = null;
    let timeRemaining  = examDuration;
    let examStarted    = false;

    const examForm       = document.getElementById('exam-form');
    const startScreen    = document.getElementById('examStartScreen');
    const floatingTimer  = document.getElementById('floatingTimer');
    const floatingDisp   = document.getElementById('floatingTimerDisplay');
    const sidebarDisp    = document.getElementById('timer-display');
    const progressFill   = document.getElementById('progressFill');
    const answeredText   = document.getElementById('answeredCountText');
    const currentQNum    = document.getElementById('currentQNum');

    // ── Start Exam Button ──
    document.getElementById('btnStartExam').addEventListener('click', function () {
        startScreen.style.display = 'none';
        floatingTimer.style.display = 'flex';
        examStarted = true;
        startTimer();
        restoreAnswers();
        setupAnswerListeners();
    });

    // ── Timer ──
    function startTimer() {
        updateTimerDisplay();
        timerInterval = setInterval(function () {
            timeRemaining--;
            updateTimerDisplay();
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                Swal.fire({
                    title: 'انتهى الوقت!',
                    text: 'سيتم تسليم إجاباتك تلقائياً الآن.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    localStorage.removeItem(storageKey);
                    examForm.submit();
                });
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const h = Math.floor(timeRemaining / 3600);
        const m = Math.floor((timeRemaining % 3600) / 60);
        const s = Math.floor(timeRemaining % 60);
        const str = (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(s);
        sidebarDisp.textContent = str;
        floatingDisp.textContent = str;

        // Danger mode last 5 min
        if (timeRemaining <= 300) {
            sidebarDisp.classList.replace('text-primary', 'text-danger');
            floatingTimer.classList.add('danger');
        } else {
            sidebarDisp.classList.remove('text-danger');
            sidebarDisp.classList.add('text-primary');
            floatingTimer.classList.remove('danger');
        }
    }
    function pad(n) { return String(n).padStart(2, '0'); }

    // ── Question Navigation ──
    window.goToQuestion = function (idx) {
        if (idx < 0 || idx >= totalQuestions) return;
        document.getElementById('qcard-' + currentIndex).style.display = 'none';
        document.getElementById('navbtn-' + currentIndex).classList.remove('current');

        currentIndex = idx;
        document.getElementById('qcard-' + idx).style.display = 'block';
        document.getElementById('navbtn-' + idx).classList.add('current');
        currentQNum.textContent = idx + 1;

        // Scroll to top of card
        document.getElementById('qcard-' + idx).scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ── Answer Listeners & Progress ──
    function setupAnswerListeners() {
        document.querySelectorAll('.student-answer').forEach(function (input) {
            input.addEventListener('change', function () { onAnswer(this); });
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                input.addEventListener('input', function () { onAnswer(this); });
            }
        });
    }

    function onAnswer(input) {
        const qindex = parseInt(input.dataset.qindex);
        const hasValue = (input.type === 'radio' || input.tagName === 'SELECT')
            ? !!document.querySelector(`[name="${input.name}"]:checked`) || input.value
            : input.value.trim() !== '';

        if (hasValue) {
            answeredSet.add(qindex);
            document.getElementById('navbtn-' + qindex)?.classList.add('answered');
            document.getElementById('qcard-' + qindex)?.classList.add('answered');
        } else {
            answeredSet.delete(qindex);
            document.getElementById('navbtn-' + qindex)?.classList.remove('answered');
            document.getElementById('qcard-' + qindex)?.classList.remove('answered');
        }

        // Update progress
        answeredText.textContent = answeredSet.size + ' / ' + totalQuestions + ' تمت الإجابة';
        progressFill.style.width = Math.round((answeredSet.size / totalQuestions) * 100) + '%';

        saveAnswersLocally();
    }

    // ── Submit ──
    window.confirmSubmit = function () {
        const unanswered = totalQuestions - answeredSet.size;
        
        let htmlContent = '<p>هل أنت متأكد من رغبتك في تسليم الاختبار؟</p>';
        if (unanswered > 0) {
            htmlContent += `<div class="alert alert-warning border-warning text-dark mt-3 py-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>تنبيه:</strong> لم تُجب عن <strong>${unanswered}</strong> سؤال بعد.
            </div>`;
        }

        Swal.fire({
            title: 'تأكيد التسليم',
            html: htmlContent,
            icon: unanswered > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'نعم، قم بالتسليم',
            cancelButtonText: 'إلغاء المراجعة',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري التسليم...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                localStorage.removeItem(storageKey);
                examForm.submit();
            }
        });
    };

    examForm.addEventListener('submit', function () {
        localStorage.removeItem(storageKey);
    });

    // ── Local Storage ──
    function saveAnswersLocally() {
        const answers = {};
        document.querySelectorAll('.student-answer').forEach(function (input) {
            if (input.type === 'radio') {
                if (input.checked) answers[input.dataset.qid] = input.value;
            } else if (input.tagName === 'SELECT') {
                if (input.value) answers[input.dataset.qid] = input.value;
            } else {
                if (input.value) answers[input.dataset.qid] = input.value;
            }
        });
        localStorage.setItem(storageKey, JSON.stringify(answers));
    }

    function restoreAnswers() {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        const answers = JSON.parse(saved);
        document.querySelectorAll('.student-answer').forEach(function (input) {
            const qid = input.dataset.qid;
            if (!answers[qid]) return;
            if (input.type === 'radio' && input.value == answers[qid]) {
                input.checked = true;
                const qindex = parseInt(input.dataset.qindex);
                answeredSet.add(qindex);
                document.getElementById('navbtn-' + qindex)?.classList.add('answered');
            } else if (input.type !== 'radio') {
                input.value = answers[qid];
                const qindex = parseInt(input.dataset.qindex);
                answeredSet.add(qindex);
                document.getElementById('navbtn-' + qindex)?.classList.add('answered');
            }
        });
        answeredText.textContent = answeredSet.size + ' / ' + totalQuestions + ' تمت الإجابة';
        progressFill.style.width = Math.round((answeredSet.size / totalQuestions) * 100) + '%';
    }

    // ── Security ──
    document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey && ['c','v','s','u','p'].includes(e.key)) ||
            e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I','C','J'].includes(e.key))) {
            e.preventDefault();
        }
    });
    window.addEventListener('blur', function () {
        if (!examStarted) return;
        document.getElementById('securityOverlay').style.display = 'flex';
        document.getElementById('examBody')?.classList.add('blur-content');
    });
    window.addEventListener('focus', function () {
        document.getElementById('securityOverlay').style.display = 'none';
        document.getElementById('examBody')?.classList.remove('blur-content');
    });
    // ── Connection Status ──
    window.addEventListener('offline', () => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'انقطع الاتصال بالإنترنت!',
            text: 'لا تقلق، يتم حفظ إجاباتك محلياً. يُرجى استعادة الاتصال قبل التسليم.',
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true,
            background: '#fee2e2',
            color: '#991b1b'
        });
    });

    window.addEventListener('online', () => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'عاد الاتصال بالإنترنت!',
            text: 'يمكنك الآن تسليم الاختبار بأمان.',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            background: '#dcfce7',
            color: '#166534'
        });
    });

    // ── Auto-Save to Server (every 15s) ──
    let autoSaveTimer = null;

    function collectAnswers() {
        const answers = {};
        document.querySelectorAll('.student-answer').forEach(function (input) {
            const qid = input.dataset.qid;
            if (!qid) return;
            if (input.type === 'radio' && input.checked) {
                answers[qid] = input.value;
            } else if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                if (input.value.trim()) answers[qid] = input.value.trim();
            } else if (input.tagName === 'SELECT') {
                if (input.value) answers[qid] = input.value;
            }
        });
        return answers;
    }

    function doAutoSave() {
        if (!examStarted) return;
        const answers = collectAnswers();
        if (Object.keys(answers).length === 0) return;

        const indicator = document.getElementById('autoSaveIndicator');

        fetch(autoSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ answers })
        })
        .then(r => r.json())
        .then(data => {
            if (indicator && data.status === 'saved') {
                indicator.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i><small class="text-success">تم الحفظ ✔️ ' + data.saved_at + '</small>';
                setTimeout(() => { indicator.innerHTML = ''; }, 5000);
            }
        })
        .catch(() => { /* silent fail — localStorage still has it */ });
    }

    // Start periodic auto-save once exam starts
    const origStart = document.getElementById('btnStartExam').onclick;
    document.getElementById('btnStartExam').addEventListener('click', function () {
        autoSaveTimer = setInterval(doAutoSave, 15000); // every 15 seconds
    });

    // Also save immediately on every answer change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('student-answer') && examStarted) {
            clearTimeout(window._saveDebouce);
            window._saveDebouce = setTimeout(doAutoSave, 1500); // debounce 1.5s
        }
    });
});
</script>
@endpush
