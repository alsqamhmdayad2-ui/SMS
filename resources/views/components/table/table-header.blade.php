@props([
    'sortable' => false,
    'sortKey' => '',
    'currentSort' => request('sort'),
    'direction' => request('direction', 'asc'),
    'class' => ''
])

<th {{ $attributes->merge(['class' => 'text-secondary fw-semibold ' . $class]) }}>
    @if($sortable)
        @php
            $isSorted = $currentSort === $sortKey;
            $newDirection = $isSorted && $direction === 'asc' ? 'desc' : 'asc';
            $icon = $isSorted ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-muted opacity-50';
            
            // Build the URL preserving current query string
            $url = request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $newDirection]);
        @endphp
        
        <a href="{{ $url }}" class="text-decoration-none text-inherit d-flex align-items-center gap-1">
            {{ $slot }}
            <i class="fas {{ $icon }} ms-1"></i>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
