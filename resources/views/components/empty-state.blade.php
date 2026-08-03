@props([
    'icon' => 'folder-open',
    'title' => 'لا توجد بيانات',
    'message' => 'لم يتم العثور على أي سجلات في هذا القسم.'
])

<div class="text-center py-5 text-muted">
    <div class="mb-3">
        <i class="fas fa-{{ $icon }} fa-3x opacity-25"></i>
    </div>
    <h5 class="fw-bold mb-2">{{ $title }}</h5>
    <p class="mb-0">{{ $message }}</p>
</div>
