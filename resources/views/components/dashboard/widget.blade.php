@props([
    'title',
    'icon' => null,
    'action' => null,
    'class' => ''
])

<x-shared.card :class="$class" shadow="sm">
    <x-slot:header>
        <div class="d-flex justify-content-between align-items-center w-100">
            <h6 class="mb-0 fw-bold">
                @if($icon) <i class="{{ $icon }} me-2 text-sms-muted"></i> @endif
                {{ $title }}
            </h6>
            @if($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    </x-slot:header>
    
    {{ $slot }}
</x-shared.card>
