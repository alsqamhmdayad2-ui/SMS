@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="logo-wrapper mx-auto mb-4 d-flex justify-content-center align-items-center bg-white shadow-sm" style="width: 140px; height: 140px; border-radius: 50%; border: 2px solid var(--primary-color, #0d2a54); overflow: hidden;">
                @if(isset($sysSettings) && $sysSettings->logo)
                    <img src="{{ asset('storage/'.$sysSettings->logo) }}" alt="شعار {{ $sysSettings->school_name }}" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;" />
                @else
                    <img src="{{ asset('assets/img/logo2.svg') }}" alt="الشعار الافتراضي" style="width: 90%; height: 90%; object-fit: contain; transform: scale(1.15);" />
                @endif
            </div>
            <h3 class="mb-1">تسجيل الدخول</h3>
            <p class="text-muted">أدخل بياناتك للمتابعة</p>
        </div>

        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="login">رقم الهوية أو البريد الإلكتروني</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                    <input type="text" id="login" name="login" class="form-control"
                           value="{{ old('login') }}" required autofocus
                           placeholder="أدخل رقم الهوية أو البريد الإلكتروني" />
                </div>
                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    يمكنك الدخول باستخدام رقم الهوية الوطنية أو البريد الإلكتروني
                </div>
                @error('login')
                    <div class="text-danger mt-1" style="font-size: 0.85rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" required />
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4 mt-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember" />
                    <label class="form-check-label" for="remember_me">تذكرني</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 14px">نسيت كلمة المرور؟</a>
                @endif
            </div>

            <button type="submit" class="btn btn-secondary w-50 mx-auto d-block btn-lg mb-3" style="border-radius: var(--border-radius-xl); font-weight: 600; transition: var(--transition);">
                دخول
            </button>
        </form>

        <div class="text-center mt-2">
            <a href="{{ url('/') }}" class="text-muted text-decoration-none" style="font-size: 14px">
                <i class="fas fa-arrow-right"></i> رجوع للرئيسية
            </a>
        </div>
    </div>
</div>


@endsection
