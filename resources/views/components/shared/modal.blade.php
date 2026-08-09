@props([
    'id',
    'title',
    'size' => 'md',
    'static' => false,
    'scrollable' => true,
    'centered' => true
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" {{ $static ? 'data-bs-backdrop="static" data-bs-keyboard="false"' : '' }}>
    <div class="modal-dialog modal-{{ $size }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }} {{ $centered ? 'modal-dialog-centered' : '' }}">
        <div class="modal-content radius-sms border-0 shadow-sms-lg">
            <div class="modal-header bg-sms-surface border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="{{ $id }}Label">{!! $title !!}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body px-4 py-4">
                @isset($body)
                    {{ $body }}
                @else
                    {{ $slot }}
                @endisset
            </div>
            
            @isset($footer)
            <div class="modal-footer bg-sms-surface border-top px-4 py-3">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>
