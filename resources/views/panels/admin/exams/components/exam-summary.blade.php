@php
    $questions = $exam->questions;
    $totalQuestions = $questions->count();
    $totalMarks = $questions->sum(fn($q) => $q->pivot->mark_override ?? $q->mark);

    // Type Distribution
    $typeCounts = $questions->groupBy(fn($q) => $q->type instanceof \App\Enums\QuestionType ? $q->type->value : $q->type)->map->count();
    
    // Difficulty Distribution
    $difficultyCounts = $questions->groupBy(fn($q) => $q->difficulty instanceof \App\Enums\QuestionDifficulty ? $q->difficulty->value : $q->difficulty)->map->count();

    // Estimated Duration
    $estimatedSeconds = $questions->sum('estimated_time');
    $estimatedMinutes = $estimatedSeconds > 0 ? round($estimatedSeconds / 60) : null;

    $isDraft = $exam->status === \App\Enums\ExamStatus::DRAFT;
    $isLocked = !$isDraft;

    $typeLabels = [
        'mcq' => 'اختيار متعدد',
        'true_false' => 'صح / خطأ',
        'short_answer' => 'إجابة قصيرة',
        'essay' => 'مقالي',
        'matching' => 'توصيل',
        'fill_blank' => 'إكمال فراغ',
    ];

    $difficultyLabels = [
        'easy' => ['label' => 'سهل', 'color' => 'success'],
        'medium' => ['label' => 'متوسط', 'color' => 'warning'],
        'hard' => ['label' => 'صعب', 'color' => 'danger'],
    ];
@endphp

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 class="card-title fw-bold text-primary mb-0"><i class="fas bg-light p-1 rounded text-primary fa-info-circle"></i> تفاصيل الامتحان</h5>
            <span class="badge bg-{{ $exam->status->badgeColor() }}">{{ $exam->status->label() }}</span>
        </div>

        {{-- Lock/Unlock Alert --}}
        @if($isLocked)
            <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fas fa-lock fs-5"></i>
                <div>
                    <strong>هذا الامتحان مقفل.</strong><br>
                    لا يمكن تعديل الأسئلة أو إضافتها أو حذفها.
                </div>
            </div>
        @endif
        
        {{-- Core Info --}}
        <ul class="list-group list-group-flush mb-3 small">
            <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="text-muted">المادة</span>
                <span class="fw-bold text-dark">{{ $exam->subject->name }}</span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="text-muted">الصف والشعبة</span>
                <span class="fw-bold text-dark">{{ $exam->schoolClass->name }} ({{ $exam->sections->pluck('name')->join('، ') }})</span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="text-muted">نوع الامتحان</span>
                <span class="badge bg-secondary text-capitalize">{{ $exam->type }}</span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="text-muted">التاريخ والوقت</span>
                <span class="fw-bold text-dark">
                    {{ $exam->exam_date?->format('Y-m-d') ?? '—' }} 
                    <br>
                    <span class="text-muted small">
                        {{ \Carbon\Carbon::parse($exam->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                    </span>
                </span>
            </li>
            @if($exam->duration_minutes)
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">المدة</span>
                    <span class="fw-bold text-primary"><i class="fas fa-hourglass-half"></i> {{ $exam->duration_formatted }}</span>
                </li>
            @endif
        </ul>

        {{-- Statistics Cards --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="border rounded p-2 text-center bg-light">
                    <div class="fs-4 fw-bold text-primary" id="questionCount">{{ $totalQuestions }}</div>
                    <div class="small text-muted">عدد الأسئلة</div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2 text-center bg-light">
                    <div class="fs-4 fw-bold text-success" id="totalMarksDisplay">{{ number_format($totalMarks, 1) }}</div>
                    <div class="small text-muted">مجموع الدرجات</div>
                </div>
            </div>
            @if($estimatedMinutes)
            <div class="col-6">
                <div class="border rounded p-2 text-center bg-light">
                    <div class="fs-5 fw-bold text-info"><i class="fas fa-clock"></i> {{ $estimatedMinutes }} دقيقة</div>
                    <div class="small text-muted">الزمن المقدر</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Type Distribution --}}
        @if($totalQuestions > 0)
        <div class="mb-3">
            <h6 class="fw-bold small text-muted mb-2"><i class="fas fa-chart-pie"></i> توزيع الأسئلة حسب النوع</h6>
            <div class="d-flex flex-wrap gap-1">
                @foreach($typeCounts as $type => $count)
                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1">
                        {{ $typeLabels[$type] ?? ucfirst($type) }}: <strong>{{ $count }}</strong>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Difficulty Distribution --}}
        <div class="mb-3">
            <h6 class="fw-bold small text-muted mb-2"><i class="fas fa-chart-bar"></i> توزيع الصعوبة</h6>
            @foreach($difficultyCounts as $diff => $count)
                @php
                    $meta = $difficultyLabels[$diff] ?? ['label' => ucfirst($diff), 'color' => 'secondary'];
                    $percentage = $totalQuestions > 0 ? round(($count / $totalQuestions) * 100) : 0;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="small" style="width: 50px;">{{ $meta['label'] }}</span>
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar bg-{{ $meta['color'] }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                    </div>
                    <span class="small fw-bold text-muted" style="width: 35px;">{{ $percentage }}%</span>
                </div>
            @endforeach
        </div>
        @endif

        @if($exam->instructions)
            <div class="mb-3 p-2 bg-light border rounded">
                <div class="fw-bold small text-muted mb-1"><i class="fas fa-sticky-note"></i> التعليمات:</div>
                <div class="small text-dark" style="white-space: pre-line;">{{ $exam->instructions }}</div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="d-grid gap-2">
            @if($isDraft)
                <form action="{{ route('admin.exams.publish', $exam->id) }}" method="POST" 
                      onsubmit="return confirm('هل أنت متأكد من نشر وقفل الامتحان؟ لن تتمكن من التعديل بعد ذلك.');">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-lock"></i> نشر وقفل الامتحان (Publish & Lock)
                    </button>
                </form>
            @else
                <form action="{{ route('admin.exams.unlock', $exam->id) }}" method="POST"
                      onsubmit="return confirm('هل تريد فتح الامتحان للتعديل؟ سيتم إعادة حالته إلى مسودة.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning w-100">
                        <i class="fas fa-unlock"></i> فتح القفل (Unlock to Draft)
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.exams.print', $exam->id) }}" target="_blank" class="btn btn-outline-dark">
                <i class="fas fa-print"></i> طباعة الامتحان (Print)
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-light text-muted">
                <i class="fas fa-arrow-left"></i> العودة للملفات
            </a>
        </div>
    </div>
</div>
