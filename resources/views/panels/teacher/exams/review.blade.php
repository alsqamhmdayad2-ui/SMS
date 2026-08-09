@extends('layouts.app')
@section('title', 'مراجعة إجابات الطالب — ' . $student->first_name . ' ' . $student->family_name)

@section('content')

<x-page-header title="مراجعة إجابات: {{ $student->first_name }} {{ $student->family_name }}">
    <x-slot:actions>
        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> عودة للإدخال
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold">{{ $exam->title }}</h5>
                <p class="text-muted mb-0">المادة: {{ $exam->subject?->name }} | المجموع الحالي: <span class="badge bg-primary fs-6">{{ $examResult->marks_obtained }} / {{ $exam->total_marks }}</span></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('teacher.exams.results.grade', [$exam, $student]) }}" method="POST">
            @csrf
            
            @foreach($exam->questions as $index => $question)
                @php 
                    $answer = $answers->get($question->id); 
                @endphp
                <div class="mb-4 pb-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold">{{ $index + 1 }}. {{ $question->question_text }}</h6>
                        <span class="badge bg-secondary">{{ $question->marks }} درجات</span>
                    </div>
                    
                    @if($question->type->value === 'mcq' || $question->type->value === 'true_false')
                        <ul class="list-group mb-3">
                            @foreach($question->options as $option)
                                @php 
                                    $isSelected = $answer && $answer->question_option_id == $option->id;
                                    $isCorrect = $option->is_correct;
                                    $class = '';
                                    if ($isSelected && $isCorrect) $class = 'list-group-item-success';
                                    elseif ($isSelected && !$isCorrect) $class = 'list-group-item-danger';
                                    elseif (!$isSelected && $isCorrect) $class = 'list-group-item-info';
                                @endphp
                                <li class="list-group-item {{ $class }}">
                                    <div class="d-flex align-items-center">
                                        @if($isSelected)
                                            <i class="fas fa-check-circle me-2"></i>
                                        @else
                                            <i class="far fa-circle me-2"></i>
                                        @endif
                                        {{ $option->option_text }}
                                        @if($isCorrect) <span class="badge bg-success ms-auto">الإجابة الصحيحة</span> @endif
                                        @if($isSelected && !$isCorrect) <span class="badge bg-danger ms-auto">اختيار الطالب</span> @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @elseif($question->type->value === 'matching')
                        @php 
                            $studentMatching = $answer && $answer->text_answer ? json_decode($answer->text_answer, true) : [];
                        @endphp
                        <table class="table table-bordered table-sm mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>الكلمة</th>
                                    <th>الإجابة الصحيحة</th>
                                    <th>إجابة الطالب</th>
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
                                                <span class="text-muted small">لم يُجب</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="mb-3">
                            <strong>إجابة الطالب:</strong>
                            <div class="p-3 bg-light rounded mt-1 border">
                                {{ $answer ? $answer->text_answer : 'لم يُجب' }}
                            </div>
                        </div>
                        @if($question->correct_answer)
                            <div class="mb-3">
                                <strong>الإجابة النموذجية:</strong>
                                <div class="p-3 bg-success bg-opacity-10 text-success rounded mt-1 border border-success">
                                    {{ $question->correct_answer }}
                                </div>
                            </div>
                        @endif
                    @endif
                    
                    <div class="d-flex align-items-center gap-2 mt-3 bg-light p-2 rounded w-50">
                        <label class="fw-bold mb-0">الدرجة الممنوحة:</label>
                        <input type="number" name="grades[{{ $question->id }}]" class="form-control form-control-sm w-25" 
                               value="{{ $answer ? $answer->marks_awarded : 0 }}" min="0" max="{{ $question->marks }}" step="0.5">
                        <span class="text-muted small">من {{ $question->marks }}</span>
                    </div>
                    
                    @if($answer && !$answer->is_graded)
                        <div class="mt-2 text-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i> بانتظار تصحيح المعلم
                        </div>
                    @endif
                </div>
            @endforeach
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> حفظ الدرجات
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
