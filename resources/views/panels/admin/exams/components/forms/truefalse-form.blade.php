<template id="truefalse-form-template">
    <div class="mb-3">
        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-patch-question"></i> الإجابة الصحيحة</h6>
        <div class="d-flex gap-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_correct_boolean" id="tfTrue" value="1" required>
                <label class="form-check-label fw-bold text-success" for="tfTrue">
                    صح (True)
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_correct_boolean" id="tfFalse" value="0" required>
                <label class="form-check-label fw-bold text-danger" for="tfFalse">
                    خطأ (False)
                </label>
            </div>
        </div>
    </div>
</template>
