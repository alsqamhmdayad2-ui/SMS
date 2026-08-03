@props([
    'title',
    'value',
    'icon' => 'chart-bar',
    'color' => 'primary',
    'trend' => null, // e.g. ['direction' => 'up', 'value' => '12%', 'text' => 'من الشهر الماضي']
    'subtitle' => null
])

<div class="stat-card {{ $color }}">
    <div class="stat-icon">
        <i class="fas fa-{{ $icon }}"></i>
    </div>
    <div class="stat-content">
        <h3 class="text-muted small mb-1">{{ $title }}</h3>
        <div class="number fs-3 fw-bold mb-1">{{ $value }}</div>
        
        @if($trend)
            <p class="status {{ $trend['direction'] === 'up' ? 'up text-success' : 'down text-danger' }} small mb-0">
                <i class="fas fa-arrow-{{ $trend['direction'] }}"></i> 
                {{ $trend['value'] }} {{ $trend['text'] ?? '' }}
            </p>
        @elseif($subtitle)
            <p class="status text-muted small mb-0">{{ $subtitle }}</p>
        @endif
    </div>
</div>
