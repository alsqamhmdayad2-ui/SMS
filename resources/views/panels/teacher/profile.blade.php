@extends('layouts.app')
@section('title', 'الملف الشخصي')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">
            <i class="fas fa-user-circle text-sms-primary me-2"></i>الملف الشخصي
        </h2>
        <p class="text-sms-muted mb-0">عرض وتحديث معلوماتك الشخصية</p>
    </div>
</div>

<x-alerts />

<div class="row g-4">

    {{-- ─── Left: Avatar Card ─── --}}
    <div class="col-lg-4">
        <x-shared.card class="text-center h-100" shadow="md">
            {{-- Avatar --}}
            @if($teacher?->avatar)
                <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Avatar"
                     class="mx-auto mb-3 rounded-circle shadow-sm"
                     style="width:110px;height:110px;object-fit:cover;">
            @else
                <div class="user-avatar mx-auto mb-3 text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                     style="width:110px;height:110px;font-size:2.5rem;background:var(--sms-gradient-primary, linear-gradient(135deg, var(--sms-primary) 0%, var(--sms-primary-dark, #0d47a1) 100%))">
                    {{ mb_strtoupper(mb_substr($teacher?->full_name ?? auth()->user()->name, 0, 1, 'UTF-8')) }}
                </div>
            @endif
            <h3 class="mb-1 fw-bold">{{ $teacher?->full_name ?? auth()->user()->name }}</h3>
            <p class="text-sms-muted mb-4">
                {{ $teacher?->specialization ? 'معلم ' . $teacher->specialization : 'معلم' }}
            </p>

            {{-- Quick Info --}}
            <div class="text-start p-4 rounded-3 border border-secondary-subtle bg-light">
                @if($teacher?->national_id)
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-white rounded p-2 border me-3 shadow-sm text-sms-primary">
                            <i class="fas fa-id-card fa-fw"></i>
                        </div>
                        <div>
                            <small class="text-sms-muted d-block fw-semibold">رقم الهوية</small>
                            <span class="fw-bold" style="direction:ltr;display:inline-block">{{ $teacher->national_id }}</span>
                        </div>
                    </div>
                @endif

                @if($subjects->isNotEmpty())
                    <div class="mb-3">
                        <small class="text-sms-muted d-block fw-semibold mb-2">
                            <i class="fas fa-book fa-fw me-1 text-sms-primary"></i>المواد التي تدرسها
                        </small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($subjects as $sub)
                                <span class="badge bg-sms-primary text-white px-2 py-1 rounded-pill shadow-sm">{{ $sub->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($sections))
                    <div>
                        <small class="text-sms-muted d-block fw-semibold mb-2">
                            <i class="fas fa-users fa-fw me-1 text-sms-primary"></i>شعبك
                        </small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($sections as $sec)
                                <span class="badge border border-sms-primary text-sms-primary px-2 py-1 rounded-pill">
                                    {{ $sec->schoolClass?->name ?? '' }} ({{ $sec->name }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-shared.card>
    </div>

    {{-- ─── Right: Edit Form ─── --}}
    <div class="col-lg-8">
        <x-shared.card shadow="md" title="تحديث المعلومات الشخصية">
            <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    {{-- Full Name (read-only) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary opacity-75">الاسم الكامل</label>
                        <input type="text" class="form-control"
                               value="{{ $teacher?->full_name ?? auth()->user()->name }}" disabled>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sms-primary">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', auth()->user()->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sms-primary">رقم الهاتف</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $teacher?->phone) }}"
                               placeholder="05xxxxxxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- National ID (read-only) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary opacity-75">رقم الهوية</label>
                        <input type="text" class="form-control"
                               value="{{ $teacher?->national_id }}" disabled>
                    </div>

                    {{-- Address --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sms-primary">العنوان</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $teacher?->address) }}"
                               placeholder="المدينة / الحي">
                    </div>

                    {{-- Specialization (read-only) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary opacity-75">التخصص</label>
                        <input type="text" class="form-control"
                               value="{{ $teacher?->specialization }}" disabled>
                    </div>

                    {{-- Avatar Upload --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold text-sms-primary">الصورة الشخصية</label>
                        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        <small class="text-sms-muted">اختر صورة لتحديث صورتك الشخصية (اختياري)</small>
                        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Change Password Section --}}
                <div class="mt-5 border-top pt-4">
                    <h5 class="mb-4 fw-bold">
                        <i class="fas fa-lock me-2 text-sms-primary"></i>تغيير كلمة المرور
                    </h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sms-primary">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="اتركه فارغاً إذا لم تريد التغيير" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sms-primary">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="أعد كتابة كلمة المرور" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="mt-5 text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                    </button>
                </div>
            </form>
        </x-shared.card>
    </div>
</div>

@endsection
