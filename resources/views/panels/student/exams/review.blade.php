@extends('layouts.app')
@section('title', 'مراجعة إجابات الاختبار — ' . $exam->title)

@section('content')

<x-page-header title="مراجعة الإجابات">
    <x-slot:actions>
        <a href="{{ route('student.exams') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة للاختبارات
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 border-start border-info border-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-2">{{ $exam->title }}</h5>
                <div class="d-flex flex-wrap gap-4 text-muted">
                    <span><i class="fas fa-book me-1"></i> المادة: {{ $exam->subject?->name }}</span>
                    <span><i class="fas fa-star text-warning me-1"></i> الدرجة الكلية: {{ $exam->total_marks }}</span>
                    <span>
                        <i class="fas fa-bullseye text-primary me-1"></i> درجتك: 
                        <span class="badge {{ $examResult->percentage >= 50 ? 'bg-success' : 'bg-danger' }} fs-6">
                            {{ (float)$examResult->marks_obtained }} 
                            ({{ (float)$examResult->percentage }}%)
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="fas fa-list-check text-info me-2"></i>تفاصيل الإجابات</h5>
        
        @foreach($exam->questions as $index => $question)
            @php 
                $answer = $answers->get($question->id); 
            @endphp
            <div class="mb-4 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold" style="font-size: 1.1rem;">
                        <span class="text-primary me-1">{{ $index + 1 }}.</span> {{ $question->question_text }}
                    </h6>
                    <span class="badge bg-light text-dark border">الدرجة المستحقة: {{ $answer ? (float)$answer->marks_awarded : 0 }} / {{ $question->marks }}</span>
                </div>
                
                @if($question->type->value === 'mcq' || $question->type->value === 'true_false')
                    <ul class="list-group mb-2 mt-3">
                        @foreach($question->options as $option)
                            @php 
                                $isSelected = $answer && $answer->question_option_id == $option->id;
                                $isCorrect = $option->is_correct;
                                $class = '';
                                if ($isSelected && $isCorrect) $class = 'list-group-item-success border-success';
                                elseif ($isSelected && !$isCorrect) $class = 'list-group-item-danger border-danger';
                                elseif (!$isSelected && $isCorrect) $class = 'list-group-item-info border-info';
                            @endphp
                            <li class="list-group-item {{ $class }}">
                                <div class="d-flex align-items-center">
                                    @if($isSelected)
                                        <i class="fas fa-check-circle me-2"></i>
                                    @else
                                        <i class="far fa-circle me-2"></i>
                                    @endif
                                    {{ $option->option_text }}
                                    
                                    @if($isCorrect && $isSelected)
                                        <span class="badge bg-success ms-auto"><i class="fas fa-check me-1"></i> إجابة صحيحة</span>
                                    @elseif($isSelected && !$isCorrect)
                                        <span class="badge bg-danger ms-auto"><i class="fas fa-times me-1"></i> إجابة خاطئة</span>
                                    @elseif(!$isSelected && $isCorrect)
                                        <span class="badge bg-info ms-auto">الإجابة الصحيحة</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @elseif($question->type->value === 'matching')
                    @php 
                        $studentMatching = $answer && $answer->text_answer ? json_decode($answer->text_answer, true) : [];
                    @endphp
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th>الكلمة</th>
                                    <th>الإجابة الصحيحة</th>
                                    <th>إجابتك</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($question->options as $option)
                                    @php
                                        $studentPicked = $studentMatching[$option->id] ?? null;
                                        $isCorrectMatch = $studentPicked === $option->right_item;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold bg-light">{{ $option->left_item }}</td>
                                        <td class="text-success">{{ $option->right_item }}</td>
                                        <td>
                                            @if($studentPicked)
                                                <span class="fw-bold {{ $isCorrectMatch ? 'text-success' : 'text-danger' }}">
                                                    {{ $studentPicked }}
                                                    @if($isCorrectMatch) <i class="fas fa-check-circle ms-1"></i> @else <i class="fas fa-times-circle ms-1"></i> @endif
                                                </span>
                                            @else
                                                <span class="text-muted small">لم تُجب</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mb-3 mt-3">
                        <strong class="text-muted small d-block mb-1">إجابتك:</strong>
                        <div class="p-3 bg-light rounded border {{ $answer && $answer->marks_awarded > 0 ? 'border-success' : ($answer && $answer->is_graded ? 'border-danger' : 'border-warning') }}">
                            {{ $answer ? $answer->text_answer : 'لم تُجب' }}
                        </div>
                    </div>
                    @if($question->correct_answer)
                        <div class="mb-3">
                            <strong class="text-muted small d-block mb-1">الإجابة النموذجية:</strong>
                            <div class="p-3 bg-success bg-opacity-10 text-success rounded border border-success">
                                {{ $question->correct_answer }}
                            </div>
                        </div>
                    @endif
                @endif
                
                @if($answer && !$answer->is_graded)
                    <div class="mt-2 text-warning small">
                        <i class="fas fa-clock me-1"></i> بانتظار تصحيح المعلم لهذا السؤال
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

@endsection
