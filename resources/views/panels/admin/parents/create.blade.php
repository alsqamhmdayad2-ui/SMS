@extends('layouts.app')
@section('title', 'إضافة ولي أمر جديد')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-tie me-2"></i> إضافة ولي أمر جديد</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">أولياء الأمور</a></li>
            <li class="breadcrumb-item active" aria-current="page">إضافة جديد</li>
        </ol>
    </nav>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.parents.store') }}" method="POST">
    @csrf
    <div class="row g-4">

        <!-- Personal Info -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle me-2"></i> البيانات الشخصية لولي الأمر</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label required">الاسم الأول</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   name="first_name" value="{{ old('first_name') }}" required />
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الأب</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ old('father_name') }}" required />
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الجد</label>
                            <input type="text" class="form-control @error('grandfather_name') is-invalid @enderror"
                                   name="grandfather_name" value="{{ old('grandfather_name') }}" required />
                            @error('grandfather_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم العائلة</label>
                            <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                   name="family_name" value="{{ old('family_name') }}" required />
                            @error('family_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">صلة القرابة</label>
                            <select class="form-select @error('guardian_type') is-invalid @enderror" name="guardian_type" required>
                                <option value="Father" {{ old('guardian_type', 'Father') == 'Father' ? 'selected' : '' }}>الأب</option>
                                <option value="Mother" {{ old('guardian_type') == 'Mother' ? 'selected' : '' }}>الأم</option>
                                <option value="Guardian" {{ old('guardian_type') == 'Guardian' ? 'selected' : '' }}>وصي قانوني</option>
                            </select>
                            @error('guardian_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">رقم الهوية</label>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                   name="national_id" value="{{ old('national_id') }}" required />
                            @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">رقم الجوال الأول</label>
                            <input type="text" class="form-control @error('phone_1') is-invalid @enderror"
                                   name="phone_1" value="{{ old('phone_1') }}" required />
                            @error('phone_1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">رقم الجوال الثاني</label>
                            <input type="text" class="form-control" name="phone_2" value="{{ old('phone_2') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المهنة</label>
                            <input type="text" class="form-control" name="occupation" value="{{ old('occupation') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">جهة العمل</label>
                            <input type="text" class="form-control" name="workplace" value="{{ old('workplace') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العنوان</label>
                            <input type="text" class="form-control" name="address" value="{{ old('address') }}"
                                   placeholder="المحافظة، المدينة، الشارع..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto Login Info -->
        <div class="col-12">
            <div class="alert border-start border-4 border-info bg-info bg-opacity-10 rounded-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-key me-1 text-info"></i> بيانات الدخول للنظام (تلقائية)</h6>
                <div class="row g-3">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <i class="fas fa-id-card text-info fs-5"></i>
                        <div>
                            <div class="small text-muted fw-semibold">اسم المستخدم (للدخول)</div>
                            <code class="bg-white rounded px-2 py-1 border d-inline-block mt-1">رقم الهوية الوطنية</code>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <i class="fas fa-lock text-info fs-5"></i>
                        <div>
                            <div class="small text-muted fw-semibold">كلمة المرور الابتدائية</div>
                            <code class="bg-white rounded px-2 py-1 border d-inline-block mt-1">رقم الهوية الوطنية</code>
                        </div>
                    </div>
                </div>
                <p class="mb-0 mt-2 small text-muted"><i class="fas fa-info-circle me-1"></i> يمكن لولي الأمر تغيير كلمة المرور بعد أول تسجيل دخول.</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary px-5">
                    <i class="fas fa-save me-1"></i> حفظ بيانات ولي الأمر
                </button>
                <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
            </div>
        </div>
    </div>
</form>

@endsection
