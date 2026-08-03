@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
    'class' => ''
])

@php
    $icons = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle'
    ];
    $iconClass = $icon ?? $icons[$type] ?? 'fas fa-info-circle';
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-' . $type . ($dismissible ? ' alert-dismissible' : '') . ' fade show radius-sms ' . $class]) }} role="alert">
    <div class="d-flex align-items-center">
        @if($icon !== false)
            <i class="{{ $iconClass }} me-2 fs-5"></i>
        @endif
        <div>
            {{ $slot }}
        </div>
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
