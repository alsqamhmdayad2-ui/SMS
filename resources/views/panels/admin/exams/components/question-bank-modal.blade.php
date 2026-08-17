<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="questionBankModalLabel">
                    <i class="fas fa-download"></i> بنك الأسئلة لمادة: {{ $exam->subject->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <!-- Filters Toolbar -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" id="bankSearch" class="form-control form-control-sm" placeholder="بحث بنص السؤال...">
                            </div>
                            <div class="col-md-3">
                                <select id="bankTypeFilter" class="form-select form-select-sm">
                                    <option value="">كل الأنواع</option>
                                    <option value="mcq">اختيار من متعدد</option>
                                    <option value="true_false">صح / خطأ</option>
                                    <option value="short_answer">إجابة قصيرة</option>
                                    <option value="essay">سؤال مقالي</option>
                                    <option value="matching">توصيل / مطابقة</option>
                                    <option value="fill_blank">إكمال الفراغ</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="bankDifficultyFilter" class="form-select form-select-sm">
                                    <option value="">كل الصعوبات</option>
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" id="refreshBankBtn" class="btn btn-sm btn-outline-secondary w-100" title="تحديث البنك">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Questions Container -->
                <div id="bankQuestionsList" style="min-height: 250px;">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        جاري تحميل الأسئلة من البنك...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
