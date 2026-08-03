@extends('layouts.app')
@section('title', 'تعديل بيانات المعلم')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-edit me-2"></i> تعديل بيانات المعلم</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">المعلمون</a></li>
            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
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

<form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST">
    @csrf
    @method('PUT')
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
                                   name="first_name" value="{{ old('first_name', $teacher->first_name) }}" required />
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الأب</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ old('father_name', $teacher->father_name) }}" required />
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">اسم الجد</label>
                            <input type="text" class="form-control @error('grandfather_name') is-invalid @enderror"
                                   name="grandfather_name" value="{{ old('grandfather_name', $teacher->grandfather_name) }}" />
                            @error('grandfather_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم العائلة</label>
                            <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                   name="family_name" value="{{ old('family_name', $teacher->family_name) }}" required />
                            @error('family_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">رقم الهوية</label>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                   name="national_id" value="{{ old('national_id', $teacher->national_id) }}" required />
                            @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone', $teacher->phone) }}" />
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العنوان</label>
                            <input type="text" class="form-control"
                                   name="address" value="{{ old('address', $teacher->address) }}" />
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
                            <label class="form-label">التخصص الرئيسي</label>
                            <input type="text" class="form-control @error('specialization') is-invalid @enderror"
                                   name="specialization" value="{{ old('specialization', $teacher->specialization) }}"
                                   placeholder="مثال: رياضيات، لغة عربية..." />
                            @error('specialization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">
                                <i class="fas fa-clock text-primary me-1"></i> النصاب الأسبوعي
                            </label>
                            <input type="number" class="form-control @error('max_weekly_periods') is-invalid @enderror"
                                   name="max_weekly_periods" value="{{ old('max_weekly_periods', $teacher->max_weekly_periods) }}" required
                                   min="0" max="40" placeholder="مثال: 24" />
                            @error('max_weekly_periods') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">المواد التي يستطيع تدريسها</label>
                            <select name="subjects[]" class="form-select select2" multiple data-placeholder="اختر المواد...">
                                @php
                                    $selectedSubjects = old('subjects', $teacher->qualifiedSubjects->pluck('id')->toArray());
                                @endphp
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ in_array($subject->id, $selectedSubjects) ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subjects') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الراتب</label>
                            <input type="number" class="form-control @error('salary') is-invalid @enderror"
                                   name="salary" value="{{ old('salary', $teacher->salary) }}" />
                            @error('salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary px-5">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
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
