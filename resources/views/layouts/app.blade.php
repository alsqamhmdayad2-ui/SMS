<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', $sysSettings->school_name ?? 'إدارة المدرسة')</title>
    
    <meta name="description" content="نظام الإدارة المدرسية - {{ $sysSettings->school_name ?? 'إدارة المدرسة' }}" />
    
    <!-- Bootstrap 5.3 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />
    <!-- FontAwesome Icons (local) -->
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/all.min.css') }}" />
    <!-- Select2 CSS for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" rel="stylesheet" />
    
    <!-- Core Application Styles (Frontend7) -->
    <link rel="stylesheet" href="{{ asset('assets/frontend7/css/base.css') }}" />
    
    <!-- SMS Core CSS Foundation -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/variables.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/core/utilities.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/core/components.css') }}" />
    
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        
        <!-- Sidebar -->
        @include('components.layout.sidebar')

        <div class="main-content">
            <!-- Navbar -->
            @include('components.layout.navbar')

            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Alerts -->
                @include('components.shared.alerts')

                @yield('content')
            </div>
            
            <!-- Footer -->
            @include('components.layout.footer')
        </div>
    </div>

    <!-- jQuery (required by Select2) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core Application Scripts (Frontend7) -->
    <script src="{{ asset('assets/frontend7/js/core.js') }}"></script>

    <!-- SMS Global Configuration -->
    <script>
        window.SMS_GLOBAL_CONFIG = {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            locale: '{{ app()->getLocale() }}',
            timezone: '{{ config("app.timezone") }}',
            currentUser: @json(auth()->user() ? ['id' => auth()->id(), 'name' => auth()->user()->name, 'role' => auth()->user()->role] : null),
            // Add specific base routes or permissions if needed
            routes: {
                base: '{{ url("/") }}'
            }
        };
    </script>

    <!-- SMS Core Architecture Scripts -->
    <script src="{{ asset('assets/js/core/app.js') }}"></script>
    <script src="{{ asset('assets/js/core/config.js') }}"></script>
    <script src="{{ asset('assets/js/core/events.js') }}"></script>
    <script src="{{ asset('assets/js/core/dom.js') }}"></script>
    <script src="{{ asset('assets/js/core/store.js') }}"></script>
    <script src="{{ asset('assets/js/core/http.js') }}"></script>
    <script src="{{ asset('assets/js/core/loader.js') }}"></script>
    <script src="{{ asset('assets/js/core/notifier.js') }}"></script>
    <script src="{{ asset('assets/js/core/base-module.js') }}"></script>
    
    <!-- SMS Utils -->
    <script src="{{ asset('assets/js/utils/form.js') }}"></script>
    <script src="{{ asset('assets/js/utils/validation.js') }}"></script>
    
    <!-- Select2 JS + Arabic Language -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ar.js"></script>
    <script>
        $.fn.select2.defaults.set('language', 'ar');
        $.fn.select2.defaults.set('dir', 'rtl');
        $.fn.select2.defaults.set('theme', 'bootstrap-5');

        $(document).ready(function() {
            // Apply Select2 on elements explicitly marked as searchable
            $('.searchable-select').select2({
                allowClear: true,
                width: '100%',
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
