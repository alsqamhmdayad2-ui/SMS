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
                                
                                @if($question->type === 'mcq' || $question->type === 'true_false')
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($question->options as $option)
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="opt_{{ $option->id }}" value="{{ $option->id }}">
                                                <label class="form-check-label w-100 p-2 rounded border" for="opt_{{ $option->id }}">
                                                    {{ $option->option_text }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->type === 'short_answer' || $question->type === 'fill_blank')
                                    <input type="text" name="answers[{{ $question->id }}]" class="form-control" placeholder="اكتب إجابتك هنا...">
                                @elseif($question->type === 'essay')
                                    <textarea name="answers[{{ $question->id }}]" class="form-control" rows="4" placeholder="اكتب إجابتك المقالية هنا..."></textarea>
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
                                    
                                    @if($question->type === 'mcq' || $question->type === 'true_false')
                                        <div class="d-flex flex-column gap-2 mb-4">
                                            @foreach($question->options as $option)
                                                <div class="form-check custom-radio">
                                                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="opt_{{ $option->id }}" value="{{ $option->id }}">
                                                    <label class="form-check-label w-100 p-3 rounded border fs-5" for="opt_{{ $option->id }}">
                                                        {{ $option->option_text }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($question->type === 'short_answer' || $question->type === 'fill_blank')
                                        <input type="text" name="answers[{{ $question->id }}]" class="form-control form-control-lg mb-4" placeholder="اكتب إجابتك هنا...">
                                    @elseif($question->type === 'essay')
                                        <textarea name="answers[{{ $question->id }}]" class="form-control mb-4" rows="6" placeholder="اكتب إجابتك المقالية هنا..."></textarea>
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
    let durationMinutes = {{ $exam->duration_minutes ?? 60 }};
    let timeRemaining = durationMinutes * 60; // in seconds
    const timerDisplay = document.getElementById('timer-display');
    const examForm = document.getElementById('exam-form');
    
    function updateTimer() {
        let minutes = Math.floor(timeRemaining / 60);
        let seconds = timeRemaining % 60;
        
        timerDisplay.innerText = 
            (minutes < 10 ? '0' : '') + minutes + ':' + 
            (seconds < 10 ? '0' : '') + seconds;
            
        if (timeRemaining <= 300) { // last 5 minutes
            timerDisplay.classList.remove('text-primary');
            timerDisplay.classList.add('text-danger');
        }
            
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            timerDisplay.innerText = "00:00";
            alert('انتهى الوقت! سيتم تسليم إجاباتك تلقائياً.');
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
