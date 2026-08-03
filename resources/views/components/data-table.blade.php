<div class="card border-0 shadow-sm mb-4">
    @if(isset($header) || isset($actions))
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
        <h5 class="mb-0 fw-bold">{{ $header ?? '' }}</h5>
        @if(isset($actions))
        <div class="d-flex gap-2">
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table data-table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    {{ $thead }}
                </thead>
                <tbody>
                    {{ $tbody }}
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($footer))
    <div class="card-footer bg-white py-3 border-top">
        {{ $footer }}
    </div>
    @endif
</div>
