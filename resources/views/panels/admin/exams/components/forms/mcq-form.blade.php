<template id="mcq-form-template">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-list-ol"></i> خيارات الاختيار من متعدد</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addMcqOptionBtn">
                <i class="bi bi-plus-circle"></i> إضافة خيار
            </button>
        </div>
        <div id="mcqContainer">
            <!-- Options dynamically added here -->
        </div>
    </div>
</template>
