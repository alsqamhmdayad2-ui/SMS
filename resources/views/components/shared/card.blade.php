@props([
    'title' => null,
    'footer' => null,
    'shadow' => 'sm',
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'sms-card ' . ($shadow ? 'shadow-sms-' . $shadow : '') . ' ' . $class]) }}>
    @if($title || isset($header))
        <div class="card-header bg-sms-surface border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            @if($title)
                <h5 class="mb-0 fw-bold">{{ $title }}</h5>
            @endif
            @isset($header)
                <div>{{ $header }}</div>
            @endisset
        </div>
    @endif
    
    <div class="card-body px-4 py-4">
        {{ $slot }}
    </div>

    @if($footer || isset($footerSlot))
        <div class="card-footer bg-sms-surface border-top px-4 py-3">
            @isset($footerSlot)
                {{ $footerSlot }}
            @else
                {{ $footer }}
            @endisset
        </div>
    @endif
</div>
