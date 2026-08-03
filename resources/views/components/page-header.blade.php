<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold">{{ $title }}</h2>
        @if(isset($breadcrumb))
            {{ $breadcrumb }}
        @endif
    </div>
    @if(isset($actions) && !empty((string) $actions))
        <div class="header-actions d-flex gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
