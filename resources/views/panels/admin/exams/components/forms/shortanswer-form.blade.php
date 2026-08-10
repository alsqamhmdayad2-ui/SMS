<template id="shortanswer-form-template">
    <div>
        <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-info-circle"></i> سيقدم الطالب إجابة نصية قصيرة. يمكنك تحديد إجابات صحيحة متعددة مقبولة.
        </div>

        <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle"></i> الإجابات الصحيحة المقبولة</h6>
        <p class="text-muted small mb-2">أضف جميع الإجابات الصحيحة المقبولة (مرادفات، أشكال مختلفة، هجاء بديل...)</p>
        <div id="shortanswerAnswersContainer">
            <!-- Rows injected here -->
        </div>
        <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addShortanswerAnswerBtn">
            <i class="fas fa-plus"></i> إضافة إجابة صحيحة أخرى
        </button>
    </div>
</template>
