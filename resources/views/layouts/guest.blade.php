<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $sysSettings->school_name ?? config('app.name', 'إدارة المدرسة') }} | @yield('title', 'الدخول')</title>
    
    <!-- Bootstrap 5.3 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}" />
</head>
<body>
    @yield('content')
    <script src="{{ asset('assets/js/core.js') }}"></script>
    @stack('scripts')
</body>
</html>
