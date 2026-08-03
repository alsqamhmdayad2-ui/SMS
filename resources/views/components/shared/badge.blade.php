@props([
    'type' => 'primary',
    'pill' => false,
    'class' => ''
])

<span {{ $attributes->merge(['class' => 'badge bg-' . $type . ' ' . ($pill ? 'rounded-pill' : 'radius-sms-sm') . ' ' . $class]) }}>
    {{ $slot }}
</span>
