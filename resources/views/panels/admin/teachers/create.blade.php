@extends('layouts.app')
@section('title', 'إضافة معلم جديد')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-chalkboard-teacher me-2"></i> إضافة معلم جديد</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">المعلمون</a></li>
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

<form action="{{ route('admin.teachers.store') }}" method="POST">
    @csrf
    <div class="row g-4">

        <!-- Personal Data -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle me-2"></i> البيانات الشخصية</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label required">الاسم الأول</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   name="first_name" value="{{ old('first_name') }}" required
                                   placeholder="أدخل الاسم الأول" />
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الأب</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ old('father_name') }}" required
                                   placeholder="أدخل اسم الأب" />
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">اسم الجد</label>
                            <input type="text" class="form-control @error('grandfather_name') is-invalid @enderror"
                                   name="grandfather_name" value="{{ old('grandfather_name') }}"
                                   placeholder="أدخل اسم الجد" />
                            @error('grandfather_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم العائلة</label>
                            <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                   name="family_name" value="{{ old('family_name') }}" required
                                   placeholder="أدخل اسم العائلة" />
                            @error('family_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">رقم الهوية</label>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                   name="national_id" value="{{ old('national_id') }}" required
                                   placeholder="أدخل رقم الهوية" />
                            @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">رقم الهاتف</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone') }}" required
                                   placeholder="أدخل رقم الهاتف" />
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العنوان</label>
                            <input type="text" class="form-control"
                                   name="address" value="{{ old('address') }}"
                                   placeholder="أدخل العنوان السكني" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Data -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-briefcase me-2"></i> البيانات المهنية</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">التخصص الرئيسي</label>
                            <input type="text" class="form-control @error('specialization') is-invalid @enderror"
                                   name="specialization" value="{{ old('specialization') }}" required
                                   placeholder="مثال: رياضيات، لغة عربية..." />
                            @error('specialization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">المواد التي يستطيع تدريسها</label>
                            <select name="subjects[]" class="form-select select2" multiple data-placeholder="اختر المواد...">
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ (collect(old('subjects'))->contains($subject->id)) ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subjects') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الراتب</label>
                            <input type="number" class="form-control @error('salary') is-invalid @enderror"
                                   name="salary" value="{{ old('salary') }}"
                                   placeholder="أدخل الراتب الشهري" />
                            @error('salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <p class="mb-0 mt-2 small text-muted">
                    <i class="fas fa-info-circle me-1"></i> يمكن للمعلم تغيير كلمة المرور بعد أول تسجيل دخول.
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary px-5">
                    <i class="fas fa-save me-1"></i> حفظ بيانات المعلم
                </button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
.select2-container--bootstrap-5 .select2-selection--multiple {
    min-height: 48px;
    padding-top: 6px;
}
.select2-container--bootstrap-5 .select2-selection__choice {
    background-color: var(--sms-primary) !important;
    color: white !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
    margin-top: 4px !important;
    font-size: 0.85rem;
}
.select2-container--bootstrap-5 .select2-selection__choice__remove {
    color: rgba(255,255,255,0.8) !important;
    margin-left: 5px !important;
    margin-right: 0 !important;
    border: none !important;
    background-color: transparent !important;
    filter: invert(1) brightness(200%) !important;
}
.select2-container--bootstrap-5 .select2-selection__choice__remove:hover {
    color: white !important;
    background-color: transparent !important;
    opacity: 1 !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('select[name="subjects[]"]').select2({
        theme: 'bootstrap-5',
        placeholder: '🔍 ابحث واختر المواد...',
        width: '100%',
        language: "ar",
        closeOnSelect: false
    });
});
</script>
@endpush
