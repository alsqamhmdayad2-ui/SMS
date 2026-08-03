<template id="matching-form-template">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-shuffle"></i> أزواج التوصيل / المطابقة</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addMatchPairBtn">
                <i class="bi bi-plus-circle"></i> إضافة زوج
            </button>
        </div>
        <div id="matchingContainer">
            <!-- Pairs dynamically added here -->
        </div>
    </div>
</template>
