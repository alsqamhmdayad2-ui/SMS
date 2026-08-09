@extends('layouts.app')
@section('title', 'أداء الاختبار — ' . $exam->title)

@section('content')

<x-page-header title="أداء الاختبار: {{ $exam->title }}" />

<div class="row g-4">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="exam-form" action="{{ route('student.exams.submit', $exam) }}" method="POST">
                    @csrf

                    @if($exam->display_mode === 'single_page')
                        {{-- Single Page Mode --}}
                        @foreach($exam->questions as $index => $question)
                            <div class="mb-5 pb-3 border-bottom">
                                <h5 class="fw-bold mb-3">{{ $index + 1 }}. {{ $question->question_text }} <span class="badge bg-secondary ms-2">{{ $question->marks }} درجات</span></h5>
                                
                                @if($question->type->value === 'mcq' || $question->type->value === 'true_false')
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($question->options as $option)
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input student-answer" type="radio" name="answers[{{ $question->id }}]" id="opt_{{ $option->id }}" value="{{ $option->id }}" data-qid="{{ $question->id }}">
                                                <label class="form-check-label w-100 p-2 rounded border" for="opt_{{ $option->id }}">
                                                    {{ $option->option_text }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->type->value === 'short_answer' || $question->type->value === 'fill_blank')
                                    <input type="text" name="answers[{{ $question->id }}]" class="form-control student-answer" placeholder="اكتب إجابتك هنا..." data-qid="{{ $question->id }}">
                                @elseif($question->type->value === 'essay')
                                    <textarea name="answers[{{ $question->id }}]" class="form-control student-answer" rows="4" placeholder="اكتب إجابتك المقالية هنا..." data-qid="{{ $question->id }}"></textarea>
                                @elseif($question->type->value === 'matching')
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                @php $rightItems = $question->options->pluck('right_item')->shuffle(); @endphp
                                                @foreach($question->options as $option)
                                                    <tr>
                                                        <td class="w-50 align-middle fw-bold bg-light">{{ $option->left_item }}</td>
                                                        <td class="w-50">
                                                            <select name="answers[{{ $question->id }}][{{ $option->id }}]" class="form-select student-answer" data-qid="{{ $question->id }}_{{ $option->id }}">
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
                            </div>
                        @endforeach
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5" onclick="return confirm('هل أنت متأكد من رغبتك في تسليم الاختبار؟')">تسليم الاختبار نهائياً</button>
                        </div>
                    @else
                        {{-- Per Question Mode --}}
                        <div id="questions-container">
                            @foreach($exam->questions as $index => $question)
                                <div class="question-step" id="step-{{ $index }}" style="display: {{ $index === 0 ? 'block' : 'none' }}">
                                    <h5 class="fw-bold mb-3">السؤال {{ $index + 1 }} من {{ $exam->questions->count() }}</h5>
                                    <h4 class="mb-4">{{ $question->question_text }} <span class="badge bg-secondary ms-2">{{ $question->marks }} درجات</span></h4>
                                    
                                    @if($question->type->value === 'mcq' || $question->type->value === 'true_false')
                                        <div class="d-flex flex-column gap-2 mb-4">
                                            @foreach($question->options as $option)
                                                <div class="form-check custom-radio">
                                                    <input class="form-check-input student-answer" type="radio" name="answers[{{ $question->id }}]" id="opt_{{ $option->id }}" value="{{ $option->id }}" data-qid="{{ $question->id }}">
                                                    <label class="form-check-label w-100 p-3 rounded border fs-5" for="opt_{{ $option->id }}">
                                                        {{ $option->option_text }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($question->type->value === 'short_answer' || $question->type->value === 'fill_blank')
                                        <input type="text" name="answers[{{ $question->id }}]" class="form-control form-control-lg mb-4 student-answer" placeholder="اكتب إجابتك هنا..." data-qid="{{ $question->id }}">
                                    @elseif($question->type->value === 'essay')
                                        <textarea name="answers[{{ $question->id }}]" class="form-control mb-4 student-answer" rows="6" placeholder="اكتب إجابتك المقالية هنا..." data-qid="{{ $question->id }}"></textarea>
                                    @elseif($question->type->value === 'matching')
                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered mb-0">
                                                <tbody>
                                                    @php $rightItems = $question->options->pluck('right_item')->shuffle(); @endphp
                                                    @foreach($question->options as $option)
                                                        <tr>
                                                            <td class="w-50 align-middle fw-bold bg-light">{{ $option->left_item }}</td>
                                                            <td class="w-50">
                                                                <select name="answers[{{ $question->id }}][{{ $option->id }}]" class="form-select form-select-lg student-answer" data-qid="{{ $question->id }}_{{ $option->id }}">
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
                                    
                                    <hr class="my-4">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="prevQuestion({{ $index }})" {{ $index === 0 ? 'disabled' : '' }}>السابق</button>
                                        
                                        @if($index === $exam->questions->count() - 1)
                                            <button type="submit" class="btn btn-success px-4" onclick="return confirm('هل أنت متأكد من رغبتك في تسليم الاختبار؟')">تسليم الاختبار</button>
                                        @else
                                            <button type="button" class="btn btn-primary px-4" onclick="nextQuestion({{ $index }})">التالي</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">الوقت المتبقي</h6>
                <div class="display-5 fw-bold text-primary mb-3" id="timer-display">
                    --:--
                </div>
                
                <hr>
                
                <div class="text-start small">
                    <p class="mb-1"><strong>المادة:</strong> {{ $exam->subject?->name }}</p>
                    <p class="mb-1"><strong>المعلم:</strong> أ. {{ $exam->teacher?->family_name ?? 'غير محدد' }}</p>
                    <p class="mb-0"><strong>الدرجة الكلية:</strong> {{ $exam->total_marks }}</p>
                </div>
                
                @if($exam->instructions)
                    <hr>
                    <div class="text-start">
                        <strong class="d-block mb-1">التعليمات:</strong>
                        <p class="small text-muted mb-0">{{ $exam->instructions }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .custom-radio .form-check-input {
        display: none;
    }
    .custom-radio .form-check-label {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .custom-radio .form-check-input:checked + .form-check-label {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary) !important;
    }
    .custom-radio .form-check-label:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    // Timer Logic
    let timeRemaining = {{ isset($remainingSeconds) ? $remainingSeconds : ($exam->duration_minutes ?? 60) * 60 }};
    const timerDisplay = document.getElementById('timer-display');
    const examForm = document.getElementById('exam-form');
    
    // Auto Save Logic
    const examId = {{ $exam->id }};
    const storageKey = `exam_${examId}_answers`;
    
    function saveAnswersLocally() {
        let answers = {};
        document.querySelectorAll('.student-answer').forEach(input => {
            if (input.type === 'radio') {
                if (input.checked) answers[input.dataset.qid] = input.value;
            } else {
                if (input.value) answers[input.dataset.qid] = input.value;
            }
        });
        localStorage.setItem(storageKey, JSON.stringify(answers));
    }
    
    function restoreAnswers() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const answers = JSON.parse(saved);
            document.querySelectorAll('.student-answer').forEach(input => {
                const qid = input.dataset.qid;
                if (answers[qid]) {
                    if (input.type === 'radio' && input.value == answers[qid]) {
                        input.checked = true;
                    } else if (input.type !== 'radio') {
                        input.value = answers[qid];
                    }
                }
            });
        }
    }
    
    // Restore on load
    document.addEventListener('DOMContentLoaded', restoreAnswers);
    
    // Save on change
    document.querySelectorAll('.student-answer').forEach(input => {
        input.addEventListener('change', saveAnswersLocally);
        if (input.type === 'text' || input.tagName === 'TEXTAREA') {
            input.addEventListener('keyup', saveAnswersLocally);
        }
    });

    // Clear local storage on submit
    examForm.addEventListener('submit', function() {
        localStorage.removeItem(storageKey);
    });
    
    function updateTimer() {
        let hours = Math.floor(timeRemaining / 3600);
        let minutes = Math.floor((timeRemaining % 3600) / 60);
        let seconds = Math.floor(timeRemaining % 60);
        
        let display = '';
        if (hours > 0) {
            display += (hours < 10 ? '0' : '') + hours + ':';
        }
        display += (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        
        timerDisplay.innerText = display;
            
        if (timeRemaining <= 300) { // last 5 minutes
            timerDisplay.classList.remove('text-primary');
            timerDisplay.classList.add('text-danger');
        }
            
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            timerDisplay.innerText = "00:00";
            alert('انتهى الوقت! سيتم تسليم إجاباتك تلقائياً.');
            localStorage.removeItem(storageKey);
            examForm.submit();
        } else {
            timeRemaining--;
        }
    }
    
    // Initial call
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);

    // Navigation Logic for per_question mode
    function nextQuestion(currentIndex) {
        document.getElementById('step-' + currentIndex).style.display = 'none';
        document.getElementById('step-' + (currentIndex + 1)).style.display = 'block';
    }
    
    function prevQuestion(currentIndex) {
        document.getElementById('step-' + currentIndex).style.display = 'none';
        document.getElementById('step-' + (currentIndex - 1)).style.display = 'block';
    }
</script>
@endpush
