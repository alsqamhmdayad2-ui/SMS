<nav aria-label="breadcrumb" class="my-3">
    <ol class="breadcrumb mb-0 small" style="background:transparent; padding:0;">
        @foreach($items as $item)
            @if($loop->last)
                <li class="breadcrumb-item active text-muted" aria-current="page">{{ $item['name'] }}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] ?? '#' }}" class="text-decoration-none text-primary">{{ $item['name'] }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
