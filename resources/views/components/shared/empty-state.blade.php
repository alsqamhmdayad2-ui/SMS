@props([
    'icon' => 'fas fa-folder-open',
    'title' => 'لا توجد بيانات',
    'description' => 'لم يتم العثور على أي سجلات لعرضها هنا.',
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'sms-empty-state ' . $class]) }}>
    <i class="{{ $icon }}"></i>
    <h5 class="fw-bold text-sms-main">{{ $title }}</h5>
    <p class="mb-0">{{ $description }}</p>
    @isset($action)
        <div class="mt-4">
            {{ $action }}
        </div>
    @endisset
</div>
