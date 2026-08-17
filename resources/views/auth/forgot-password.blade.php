@extends('layouts.guest')

@section('title', 'استعادة كلمة المرور')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="logo-wrapper mx-auto mb-4 d-flex justify-content-center align-items-center bg-white shadow-sm" style="width: 140px; height: 140px; border-radius: 50%; border: 2px solid var(--primary-color, #0d2a54);">
                <img src="{{ asset('assets/img/logo2.svg') }}" alt="شعار {{ $sysSettings->school_name ?? config('app.name', 'إدارة المدرسة') }}" style="width: 90%; height: 90%; object-fit: contain; transform: scale(1.15);" />
            </div>
            <h3 class="mb-1">استعادة كلمة المرور</h3>
            <p class="text-muted" style="font-size: 14px;">نسيت كلمة المرور؟ أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة التعيين.</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus />
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mx-auto d-block btn-lg mb-3 shadow-sm" style="border-radius: var(--border-radius-xl); font-weight: 600; transition: var(--transition);">
                إرسال رابط إعادة التعيين
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-muted text-decoration-none" style="font-size: 14px">
                <i class="fas fa-arrow-right"></i> العودة لتسجيل الدخول
            </a>
        </div>
    </div>
</div>
@endsection
