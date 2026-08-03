<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <div class="row align-items-center g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchExamQuestions" class="form-control bg-light border-start-0" placeholder="بحث في أسئلة الامتحان...">
                </div>
            </div>
            <div class="col-md-8 text-md-end d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                <span class="text-muted small me-2">إجمالي الأسئلة: <strong id="questionCount" class="text-dark">{{ $exam->question_count }}</strong></span>
                @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionBankModal">
                    <i class="bi bi-box-arrow-in-down"></i> استيراد من بنك الأسئلة
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
