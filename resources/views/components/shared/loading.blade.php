@props([
    'text' => 'جاري التحميل...',
    'size' => 'md',
    'color' => 'primary',
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'd-flex justify-content-center align-items-center py-4 ' . $class]) }}>
    <div class="spinner-border text-sms-{{ $color }} spinner-border-{{ $size }} me-2" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    @if($text)
        <span class="text-sms-muted fw-semibold">{{ $text }}</span>
    @endif
</div>
