@php
    $hasRoles = isset($item['roles']);
    $userHasRole = $hasRoles ? auth()->user()->hasAnyRole($item['roles']) : true;
    
    $hasSubmenu = isset($item['items']) && count($item['items']) > 0;
    
    // Check if any submenu item is active to expand it
    $isActive = false;
    if ($hasSubmenu) {
        foreach ($item['items'] as $subItem) {
            if (isset($subItem['route']) && Route::is($subItem['route'])) {
                $isActive = true;
                break;
            }
        }
    } else {
        $isActive = isset($item['route']) && Route::is($item['route']);
    }
    
    $submenuId = 'submenu-' . \Illuminate\Support\Str::slug($item['title']);
@endphp

@if($userHasRole)
    @if($hasSubmenu)
        <li class="has-submenu">
            <button class="menu-toggle {{ $isActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#{{ $submenuId }}" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="{{ $submenuId }}">
                <i class="fas fa-{{ $item['icon'] ?? 'circle' }}"></i><span class="sidebar-text">{{ $item['title'] }}</span>
            </button>
            <ul class="submenu collapse {{ $isActive ? 'show' : '' }}" id="{{ $submenuId }}">
                @foreach($item['items'] as $subItem)
                    @php
                        $subHasRoles = isset($subItem['roles']);
                        $subUserHasRole = $subHasRoles ? auth()->user()->hasAnyRole($subItem['roles']) : true;
                    @endphp
                    @if($subUserHasRole)
                        <li><a href="{{ isset($subItem['route']) ? route($subItem['route']) : '#' }}" class="{{ (isset($subItem['route']) && Route::is($subItem['route'])) ? 'active' : '' }}">{{ $subItem['name'] }}</a></li>
                    @endif
                @endforeach
            </ul>
        </li>
    @else
        <li>
            <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                <i class="fas fa-{{ $item['icon'] ?? 'circle' }}"></i><span class="sidebar-text">{{ $item['title'] }}</span>
            </a>
        </li>
    @endif
@endif
