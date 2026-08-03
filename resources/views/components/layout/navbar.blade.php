<header class="top-header">
    <div class="header-right">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="بحث سريع...">
        </div>
    </div>
    <div class="header-left">
        <button class="header-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
        <div class="user-dropdown">
            <div class="user-info text-end">
                <div class="name">{{ auth()->user()->name ?? 'مستخدم' }}</div>
                <div class="role">
                    @if(auth()->user()?->hasRole('admin'))
                        مدير النظام
                    @elseif(auth()->user()?->hasRole('teacher'))
                        معلم
                    @elseif(auth()->user()?->hasRole('student'))
                        طالب
                    @elseif(auth()->user()?->hasRole('parent'))
                        ولي أمر
                    @else
                        مستخدم
                    @endif
                </div>
            </div>
            <div class="user-avatar" style="background:var(--gradient-primary);">
                {{ mb_substr(auth()->user()->name ?? 'م', 0, 2) }}
            </div>
        </div>
    </div>
</header>
