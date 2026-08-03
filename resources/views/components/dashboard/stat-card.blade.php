@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'primary',
    'trend' => null, // 'up', 'down', or null
    'trendValue' => null,
    'trendText' => 'منذ الشهر الماضي',
    'class' => ''
])

<x-shared.card :class="'h-100 ' . $class" shadow="sm">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h6 class="text-sms-muted fw-semibold mb-2">{{ $title }}</h6>
            <h3 class="fw-bold mb-0 text-sms-main">{{ $value }}</h3>
            
            @if($trend !== null)
                <div class="mt-2 text-sm">
                    <span class="fw-semibold text-{{ $trend === 'up' ? 'sms-success' : 'sms-danger' }}">
                        <i class="fas fa-arrow-{{ $trend }} me-1"></i>
                        {{ $trendValue }}
                    </span>
                    <span class="text-sms-muted ms-1">{{ $trendText }}</span>
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="rounded p-3 bg-sms-{{ $color }} bg-opacity-10 text-sms-{{ $color }}">
                <i class="{{ $icon }} fs-4"></i>
            </div>
        @endif
    </div>
</x-shared.card>
