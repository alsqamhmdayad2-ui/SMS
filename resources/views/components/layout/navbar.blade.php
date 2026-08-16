<header class="top-header">
    <div class="header-right">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="search-box d-none d-md-flex">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="بحث سريع...">
        </div>
    </div>
    <div class="header-left d-flex align-items-center gap-3">
        <!-- Messages/Notifications Dropdown -->
        @php
            $unreadMessages = \App\Models\MessageRecipient::where('receiver_id', auth()->id())
                                ->whereNull('read_at')
                                ->with('message.sender')
                                ->latest()
                                ->take(5)
                                ->get();
            $unreadCount = \App\Models\MessageRecipient::where('receiver_id', auth()->id())
                                ->whereNull('read_at')
                                ->count();
        @endphp
        <div class="dropdown">
            <button class="header-icon-btn position-relative bg-transparent border-0" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.25rem; color: #64748b;">
                <i class="fas fa-bell"></i>
                @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    {{ $unreadCount }}
                </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <li><h6 class="dropdown-header fw-bold text-end">الإشعارات والرسائل</h6></li>
                @forelse($unreadMessages as $rec)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 border-bottom" href="{{ route('messages.show', $rec->message_id) }}">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="text-truncate text-end w-100">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $rec->message->sender->name ?? 'مستخدم' }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.8rem; max-width: 200px;">{{ $rec->message->subject }}</div>
                        </div>
                    </a>
                </li>
                @empty
                <li><span class="dropdown-item text-muted text-center py-3">لا توجد إشعارات جديدة</span></li>
                @endforelse
                <li><a class="dropdown-item text-center text-primary fw-bold py-2" href="{{ route('messages.index') }}">عرض كل الرسائل</a></li>
            </ul>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <div class="user-dropdown d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="user-info text-end d-none d-md-block">
                    <div class="name fw-bold text-dark" style="font-size: 0.9rem;">{{ auth()->user()->name ?? 'مستخدم' }}</div>
                    <div class="role text-muted" style="font-size: 0.8rem;">
                        @if(auth()->user()?->hasRole('admin')) مدير النظام
                        @elseif(auth()->user()?->hasRole('teacher')) معلم
                        @elseif(auth()->user()?->hasRole('student')) طالب
                        @elseif(auth()->user()?->hasRole('parent')) ولي أمر
                        @else مستخدم @endif
                    </div>
                </div>
                <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); font-weight: bold; font-size: 1.2rem;">
                    {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 text-end">
                <li>
                    @php
                        $profileRoute = '#';
                        if (auth()->user()?->hasRole('admin')) $profileRoute = route('profile.edit');
                        elseif (auth()->user()?->hasRole('teacher')) $profileRoute = route('teacher.profile');
                        elseif (auth()->user()?->hasRole('student')) $profileRoute = route('student.profile');
                        elseif (auth()->user()?->hasRole('parent')) $profileRoute = route('parent.profile');
                    @endphp
                    <a class="dropdown-item py-2" href="{{ $profileRoute }}"><i class="fas fa-user-circle ms-2 text-secondary"></i> الملف الشخصي</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="fas fa-sign-out-alt ms-2"></i> تسجيل الخروج</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
