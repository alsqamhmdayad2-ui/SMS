@extends('layouts.app')
@section('title', 'إضافة مستخدم جديد')

@section('content')

<x-page-header title="إضافة مستخدم جديد">
    <x-slot:actions>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المستخدمين', 'url' => route('admin.users.index')],
    ['name' => 'إضافة مستخدم']
]" />

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-user-plus text-primary me-2"></i> بيانات المستخدم الجديد</h5>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="name">الاسم الكامل <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="أدخل الاسم الكامل" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="example@school.com" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="role">الدور / الصلاحية <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">-- اختر الدور --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ match($role->name) {
                                            'admin' => 'مدير النظام (Admin)',
                                            'teacher' => 'معلم (Teacher)',
                                            'student' => 'طالب (Student)',
                                            'parent' => 'ولي أمر (Parent)',
                                            default => $role->name
                                        } }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">كلمة المرور <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" placeholder="كلمة مرور قوية">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="password_confirmation">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" placeholder="أعد كتابة كلمة المرور">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> حفظ المستخدم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
