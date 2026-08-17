<div class="card mb-3 border-0 shadow-sm question-item" id="question-{{ $q->id }}" data-id="{{ $q->id }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Drag Handle (only in draft) -->
                @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                <span class="btn btn-sm btn-light cursor-move drag-handle p-1" title="اسحب لإعادة الترتيب">
                    <i class="fas fa-grip-vertical fs-5"></i>
                </span>
                @endif
                <span class="badge bg-secondary">سؤال {{ $index + 1 }}</span>
                <span class="badge bg-info text-dark text-uppercase">{{ str_replace('_', ' ', $q->type->value) }}</span>
                <span class="badge bg-success">{{ (float)($q->pivot->mark_override ?? $q->mark) }} درجة</span>
                <span class="badge bg-{{ $q->difficulty->badgeColor() }}">{{ $q->difficulty->label() }}</span>
                
                @if($q->estimated_time)
                    <span class="badge bg-light text-dark border"><i class="fas fa-clock"></i> {{ $q->estimated_time_formatted }}</span>
                @endif
                @if($q->question_code)
                    <span class="badge bg-light text-muted border">{{ $q->question_code }}</span>
                @endif
                @if($q->version > 1)
                    <span class="badge bg-dark bg-opacity-75"><i class="fas fa-layer-group"></i> v{{ $q->version }}</span>
                @endif
            </div>
            
            @if($exam->status === \App\Enums\ExamStatus::DRAFT)
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-warning edit-question" 
                        data-id="{{ $q->id }}" 
                        data-type="{{ $q->type->value }}"
                        data-text="{{ $q->question_text }}"
                        data-mark="{{ $q->pivot->mark_override ?? $q->mark }}"
                        data-difficulty="{{ $q->difficulty->value }}"
                        data-estimated-time="{{ $q->estimated_time }}"
                        data-public="{{ $q->is_public ? '1' : '0' }}"
                        data-options='@json($q->options)'
                        title="تعديل السؤال">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary duplicate-question" data-id="{{ $q->id }}" title="تكرار السؤال">
                    <i class="fas fa-copy"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-question" data-id="{{ $q->id }}" title="حذف من الامتحان">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endif
        </div>

        <p class="mb-2 mt-3 fs-5 text-dark question-text-content">{{ $q->question_text }}</p>
        
        @if($q->type->value === 'mcq')
            <ul class="list-group list-group-flush mt-2 border rounded">
                @foreach($q->options as $opt)
                    <li class="list-group-item {{ $opt->is_correct ? 'list-group-item-success fw-bold text-success' : '' }}">
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
            <div class="mt-2 p-2 bg-light border rounded">
                @php $correctOption = $q->options->first(); @endphp
                <span class="fw-bold text-success">
                    <i class="fas fa-check-circle"></i> الإجابة الصحيحة: 
                    {{ $correctOption && $correctOption->is_correct ? 'True (صح)' : 'False (خطأ)' }}
                </span>
            </div>
        @elseif($q->type->value === 'matching')
            <div class="row mt-2 g-2">
                @foreach($q->options as $opt)
                    <div class="col-12 col-md-6 mb-1">
                        <div class="p-2 border bg-light rounded d-flex justify-content-between align-items-center">
                            <span>{{ $opt->left_item }} <i class="fas fa-arrow-right mx-2 text-primary"></i> {{ $opt->right_item }}</span>
                            @if($opt->partial_mark)
                                <span class="badge bg-secondary">الدرجة الجزئية: {{ (float)$opt->partial_mark }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
