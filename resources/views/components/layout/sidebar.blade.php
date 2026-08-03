<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">
            @if(isset($sysSettings) && $sysSettings->logo)
                <img src="{{ asset('storage/'.$sysSettings->logo) }}" alt="Logo" class="logo-brand-img" style="border-radius:8px;object-fit:contain">
            @else
                <img src="{{ asset('assets/frontend7/assets/img/logo2.svg') }}" alt="Logo" class="logo-brand-img">
            @endif
        </div>
        <span class="sidebar-text">{{ $sysSettings->school_name ?? 'إدارة المدرسة' }}</span>
    </div>

    <ul class="sidebar-nav" id="sidebar-nav">
        @php
            /*
             * Filter sidebar items for the currently authenticated user's role.
             * Items without 'roles' key are shown to all authenticated users.
             */
            $allItems    = config('sidebar', []);
            $currentUser = auth()->user();
        @endphp

        @foreach($allItems as $item)
            @php
                // Determine if this item is allowed for the current user's role(s)
                $allowedRoles = $item['roles'] ?? null;
                $canSee = $allowedRoles
                    ? ($currentUser && $currentUser->hasAnyRole($allowedRoles))
                    : true;
            @endphp

            @if($canSee)
                @include('components.sidebar-item', ['item' => $item])
            @endif
        @endforeach

        {{-- Logout item (always last) --}}
        <li>
            <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display:none;">
                @csrf
            </form>
            <a href="#"
               class="nav-link do-not-remove"
               data-action="logout"
               aria-label="تسجيل الخروج">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                <span class="sidebar-text">تسجيل الخروج</span>
            </a>
        </li>
    </ul>
</aside>
