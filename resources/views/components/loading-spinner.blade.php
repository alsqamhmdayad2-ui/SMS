@props(['size' => 'md', 'color' => 'primary', 'text' => 'جاري التحميل...'])

<div class="text-center p-4">
    <div class="spinner-border text-{{ $color }} {{ $size == 'sm' ? 'spinner-border-sm' : '' }}" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    @if($text)
        <div class="mt-2 text-muted small">{{ $text }}</div>
    @endif
</div>
