@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'label' => null,
    'height' => 'md', // sm, md, lg
    'class' => ''
])

@php
    $percentage = $max > 0 ? min(100, max(0, ($value / $max) * 100)) : 0;
    
    $heights = [
        'sm' => '5px',
        'md' => '10px',
        'lg' => '15px'
    ];
    $h = $heights[$height] ?? $heights['md'];
@endphp

<div {{ $attributes->merge(['class' => 'sms-progress-wrapper ' . $class]) }}>
    @if($label || isset($slot))
        <div class="d-flex justify-content-between align-items-center mb-1 text-sm">
            @if($label) <span class="fw-semibold">{{ $label }}</span> @endif
            @if(trim($slot)) <span>{{ $slot }}</span> @endif
        </div>
    @endif
    
    <div class="progress bg-sms-light radius-sms-pill" style="height: {{ $h }}">
        <div class="progress-bar bg-sms-{{ $color }} radius-sms-pill transition-sms" 
             role="progressbar" 
             style="width: {{ $percentage }}%" 
             aria-valuenow="{{ $value }}" 
             aria-valuemin="0" 
             aria-valuemax="{{ $max }}">
        </div>
    </div>
</div>
