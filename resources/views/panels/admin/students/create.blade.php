@extends('layouts.app')
@section('title', 'إضافة طالب جديد')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-plus me-2"></i>تسجيل طالب جديد</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">الطلاب</a></li>
            <li class="breadcrumb-item active">إضافة</li>
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

<form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">

        {{-- ─── البيانات الشخصية ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle me-2"></i>البيانات الشخصية</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- صورة الطالب --}}
                        <div class="col-md-12 mb-2">
                            <div class="d-flex align-items-center gap-4">
                                <div id="avatarPreviewWrap" class="text-center">
                                    <div id="avatarPreview" style="width:90px;height:90px;border-radius:50%;background:var(--gradient-secondary);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;overflow:hidden;border:3px solid var(--border-color)">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <small class="text-muted d-block mt-1">صورة الطالب</small>
                                </div>
                                <div>
                                    <label class="form-label">رفع صورة شخصية (اختياري)</label>
                                    <input type="file" class="form-control" name="avatar" id="avatarInput"
                                           accept="image/*" onchange="previewAvatar(this)" />
                                    <small class="text-muted">JPG، PNG — الحجم الأقصى 2 ميغابايت</small>
                                </div>
                            </div>
                        </div>

                        {{-- الاسم الرباعي --}}
                        <div class="col-md-3">
                            <label class="form-label required">الاسم الأول</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   name="first_name" value="{{ old('first_name') }}" required />
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الأب</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ old('father_name') }}" required />
                            @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الجد</label>
                            <input type="text" class="form-control @error('grandfather_name') is-invalid @enderror"
                                   name="grandfather_name" value="{{ old('grandfather_name') }}" required />
                            @error('grandfather_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم العائلة</label>
                            <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                   name="family_name" value="{{ old('family_name') }}" required />
                            @error('family_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- الاسم بالإنجليزية --}}
                        <div class="col-md-3">
                            <label class="form-label">الاسم بالإنجليزية</label>
                            <input type="text" class="form-control" name="english_name"
                                   value="{{ old('english_name') }}" placeholder="Full name in English" />
                        </div>

                        {{-- رقم الهوية --}}
                        <div class="col-md-3">
                            <label class="form-label required">رقم الهوية / جواز السفر</label>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                   name="national_id" value="{{ old('national_id') }}" required />
                            @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="col-md-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control" name="phone"
                                   value="{{ old('phone') }}" placeholder="05xxxxxxxx" />
                        </div>

                        {{-- تاريخ الميلاد --}}
                        <div class="col-md-3">
                            <label class="form-label required">تاريخ الميلاد</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                   name="birth_date" value="{{ old('birth_date') }}" required />
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- مكان الولادة --}}
                        <div class="col-md-3">
                            <label class="form-label">مكان الولادة</label>
                            <input type="text" class="form-control" name="place_of_birth"
                                   value="{{ old('place_of_birth') }}" />
                        </div>

                        {{-- الجنس --}}
                        <div class="col-md-3">
                            <label class="form-label required">الجنس</label>
                            <select class="form-select @error('gender') is-invalid @enderror" name="gender" required>
                                <option value="">اختر الجنس</option>
                                <option value="Male"   {{ old('gender') == 'Male'   ? 'selected' : '' }}>ذكر</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- الجنسية --}}
                        <div class="col-md-3">
                            <label class="form-label required">الجنسية</label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                                   name="nationality" value="{{ old('nationality', 'فلسطيني') }}" required />
                            @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- فصيلة الدم --}}
                        <div class="col-md-3">
                            <label class="form-label">فصيلة الدم</label>
                            <select class="form-select" name="blood_type">
                                <option value="">اختر الفصيلة</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- الديانة --}}
                        <div class="col-md-3">
                            <label class="form-label">الديانة</label>
                            <select class="form-select" name="religion">
                                <option value="Muslim"    {{ old('religion', 'Muslim') == 'Muslim'    ? 'selected' : '' }}>مسلم</option>
                                <option value="Christian" {{ old('religion') == 'Christian' ? 'selected' : '' }}>مسيحي</option>
                            </select>
                        </div>

                        {{-- الحالة الصحية --}}
                        <div class="col-md-3">
                            <label class="form-label">الحالة الصحية</label>
                            <input type="text" class="form-control" name="health_status"
                                   value="{{ old('health_status') }}" placeholder="سليم، يعاني من..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── بيانات ولي الأمر ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-tie me-2"></i>بيانات ولي الأمر</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- البحث عن ولي أمر مسجل --}}
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold text-primary">
                                <i class="fas fa-search me-1"></i>ابحث عن ولي أمر مسجل مسبقاً (اختياري)
                            </label>
                            <select class="form-select searchable-select" name="parent_id" data-placeholder="-- اختر ولي الأمر --" style="width: 100%;">
                                <option value="" selected>اكتب للبحث بالاسم أو رقم الهوية، أو اترك فارغاً لإضافة ولي أمر جديد...</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->full_name }} ({{ $parent->national_id }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">إذا اخترت ولي أمر من القائمة، تُتجاهل الحقول أدناه تلقائياً.</small>
                        </div>

                        <div class="col-12"><hr class="my-1"><p class="text-muted mb-2 small">أو أضف ولي أمر جديد:</p></div>

                        {{-- صلة القرابة --}}
                        <div class="col-md-3">
                            <label class="form-label">صلة القرابة</label>
                            <select class="form-select" name="guardian_type">
                                <option value="Father"   {{ old('guardian_type', 'Father') == 'Father'   ? 'selected' : '' }}>الأب</option>
                                <option value="Mother"   {{ old('guardian_type') == 'Mother'   ? 'selected' : '' }}>الأم</option>
                                <option value="Guardian" {{ old('guardian_type') == 'Guardian' ? 'selected' : '' }}>وصي قانوني</option>
                            </select>
                        </div>

                        {{-- الاسم الأول --}}
                        <div class="col-md-2">
                            <label class="form-label">الاسم الأول</label>
                            <input type="text" class="form-control" name="parent_first_name" value="{{ old('parent_first_name') }}" />
                        </div>
                        {{-- اسم الأب --}}
                        <div class="col-md-2">
                            <label class="form-label">اسم الأب</label>
                            <input type="text" class="form-control" name="parent_father_name" value="{{ old('parent_father_name') }}" />
                        </div>
                        {{-- اسم الجد --}}
                        <div class="col-md-2">
                            <label class="form-label">اسم الجد</label>
                            <input type="text" class="form-control" name="parent_grandfather_name" value="{{ old('parent_grandfather_name') }}" />
                        </div>
                        {{-- اسم العائلة --}}
                        <div class="col-md-3">
                            <label class="form-label">اسم العائلة</label>
                            <input type="text" class="form-control" name="parent_family_name" value="{{ old('parent_family_name') }}" />
                        </div>

                        {{-- رقم هوية ولي الأمر --}}
                        <div class="col-md-3">
                            <label class="form-label">رقم الهوية</label>
                            <input type="text" class="form-control" name="parent_national_id" value="{{ old('parent_national_id') }}" />
                        </div>

                        {{-- رقم الجوال الأول --}}
                        <div class="col-md-3">
                            <label class="form-label">رقم الجوال الأول</label>
                            <input type="text" class="form-control" name="parent_phone_1" value="{{ old('parent_phone_1') }}" />
                        </div>

                        {{-- رقم الجوال الثاني --}}
                        <div class="col-md-3">
                            <label class="form-label">رقم الجوال الثاني</label>
                            <input type="text" class="form-control" name="parent_phone_2" value="{{ old('parent_phone_2') }}" />
                        </div>

                        {{-- المهنة --}}
                        <div class="col-md-3">
                            <label class="form-label">المهنة</label>
                            <input type="text" class="form-control" name="parent_occupation" value="{{ old('parent_occupation') }}" />
                        </div>

                        {{-- جهة العمل --}}
                        <div class="col-md-4">
                            <label class="form-label">جهة العمل</label>
                            <input type="text" class="form-control" name="parent_workplace" value="{{ old('parent_workplace') }}" />
                        </div>

                        {{-- العنوان --}}
                        <div class="col-md-8">
                            <label class="form-label">العنوان</label>
                            <input type="text" class="form-control" name="parent_address" value="{{ old('parent_address') }}" placeholder="اتركه فارغاً لاعتماد عنوان السكن الخاص بالطالب..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── عنوان السكن ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-map-marker-alt me-2"></i>عنوان السكن</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label required">المحافظة</label>
                            <select class="form-select @error('governorate') is-invalid @enderror" name="governorate" required>
                                <option value="">اختر المحافظة</option>
                                @foreach(['شمال غزة', 'غزة', 'الوسطى', 'خانيونس', 'رفح'] as $gov)
                                    <option value="{{ $gov }}" {{ old('governorate') == $gov ? 'selected' : '' }}>{{ $gov }}</option>
                                @endforeach
                            </select>
                            @error('governorate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">المدينة / البلدة</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   name="city" value="{{ old('city') }}" required />
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المنطقة / الحي</label>
                            <input type="text" class="form-control" name="neighborhood" value="{{ old('neighborhood') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المنطقة الإدارية</label>
                            <input type="text" class="form-control" name="region" value="{{ old('region') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشارع</label>
                            <input type="text" class="form-control" name="street" value="{{ old('street') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">أقرب معلم / نقطة بارزة</label>
                            <input type="text" class="form-control" name="nearest_landmark" value="{{ old('nearest_landmark') }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── البيانات الأكاديمية ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap me-2"></i>البيانات الأكاديمية</h3>
                </div>
                <div class="card-body">
                    @php
                        $reqSectionId = old('section_id') ?? request('section_id');
                        $reqClassId   = old('grade_id');
                        $reqStageId   = old('stage_id');

                        if (request('section_id') && !old('section_id')) {
                            $sel = \App\Models\Section::with('schoolClass.grade')->find(request('section_id'));
                            if ($sel) {
                                $reqClassId = $sel->class_id;
                                $reqStageId = $sel->schoolClass->grade_id ?? null;
                            }
                        }
                    @endphp
                    <div class="row g-3">
                        {{-- المرحلة --}}
                        <div class="col-md-3">
                            <label class="form-label required">المرحلة الدراسية</label>
                            <select class="form-select" name="stage_id" id="stage_id" required>
                                <option value="">اختر المرحلة</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ $reqStageId == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- الصف --}}
                        <div class="col-md-3">
                            <label class="form-label required">الصف</label>
                            <select class="form-select" name="grade_id" id="grade_id" required {{ $reqStageId ? '' : 'disabled' }}>
                                <option value="">اختر الصف</option>
                            </select>
                        </div>

                        {{-- الشعبة --}}
                        <div class="col-md-3">
                            <label class="form-label required">الشعبة</label>
                            <select class="form-select" name="section_id" id="section_id" required {{ $reqClassId ? '' : 'disabled' }}>
                                <option value="">اختر الشعبة</option>
                            </select>
                        </div>

                        {{-- تاريخ الالتحاق --}}
                        <div class="col-md-3">
                            <label class="form-label required">تاريخ الالتحاق</label>
                            <input type="date" class="form-control" name="registration_date"
                                   value="{{ old('registration_date', date('Y-m-d')) }}" required />
                        </div>

                        {{-- نوع التسجيل --}}
                        <div class="col-md-3">
                            <label class="form-label required">نوع التسجيل</label>
                            <select class="form-select" name="registration_type" required>
                                <option value="New"         {{ old('registration_type', 'New') == 'New'         ? 'selected' : '' }}>طالب جديد</option>
                                <option value="Transferred" {{ old('registration_type') == 'Transferred' ? 'selected' : '' }}>منقول</option>
                                <option value="Re-enrolled" {{ old('registration_type') == 'Re-enrolled' ? 'selected' : '' }}>إعادة قيد</option>
                            </select>
                        </div>

                        {{-- المدرسة السابقة --}}
                        <div class="col-md-3">
                            <label class="form-label">المدرسة السابقة (إن وجدت)</label>
                            <input type="text" class="form-control" name="previous_school" value="{{ old('previous_school') }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── ملاحظة بيانات الدخول ─── --}}
        <div class="col-12 mt-1">
            <div class="alert border-start border-4 border-info bg-info bg-opacity-10 rounded-3 mb-0" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fas fa-key text-info fs-5"></i>
                    <h6 class="fw-bold mb-0 text-info">بيانات الدخول للنظام (تُولَّد تلقائياً)</h6>
                </div>
                <ul class="mb-0 small text-muted">
                    <li>اسم المستخدم: <strong>رقم الهوية</strong></li>
                    <li>كلمة المرور الافتراضية: <strong>رقم الهوية</strong> (يمكن تغييرها لاحقاً)</li>
                </ul>
            </div>
        </div>

        {{-- ─── أزرار الحفظ ─── --}}
        <div class="col-12 mt-2 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="fas fa-save me-1"></i>حفظ الطالب
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
@php
    $gradesJsonData = $grades->map(function($g) {
        return [
            'id' => $g->id,
            'classes' => $g->classes->map(function($c) {
                return [
                    'id'       => $c->id,
                    'name'     => $c->name,
                    'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values(),
                ];
            })->values(),
        ];
    })->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradesData    = @json($gradesJsonData);
    const stageSelect   = document.getElementById('stage_id');
    const classSelect   = document.getElementById('grade_id');
    const sectionSelect = document.getElementById('section_id');

    const oldClassId   = '{{ $reqClassId ?? '' }}';
    const oldSectionId = '{{ $reqSectionId ?? '' }}';

    function updateClasses() {
        const stageId = stageSelect.value;
        classSelect.innerHTML   = '<option value="">اختر الصف</option>';
        sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';
        sectionSelect.disabled = true;

        if (!stageId) { classSelect.disabled = true; return; }

        classSelect.disabled = false;
        const stage = gradesData.find(g => g.id == stageId);
        if (stage && stage.classes) {
            stage.classes.forEach(cls => {
                const sel = oldClassId == cls.id ? 'selected' : '';
                classSelect.innerHTML += `<option value="${cls.id}" ${sel}>${cls.name}</option>`;
            });
        }
        if (classSelect.value) updateSections();
    }

    function updateSections() {
        const stageId = stageSelect.value;
        const classId = classSelect.value;
        sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';
        if (!classId) { sectionSelect.disabled = true; return; }

        sectionSelect.disabled = false;
        const stage = gradesData.find(g => g.id == stageId);
        if (stage) {
            const cls = stage.classes.find(c => c.id == classId);
            if (cls && cls.sections) {
                cls.sections.forEach(sec => {
                    const sel = oldSectionId == sec.id ? 'selected' : '';
                    sectionSelect.innerHTML += `<option value="${sec.id}" ${sel}>${sec.name}</option>`;
                });
            }
        }
    }

    stageSelect.addEventListener('change', updateClasses);
    classSelect.addEventListener('change', updateSections);

    if (stageSelect.value) updateClasses();
});

function previewAvatar(input) {
    const preview = document.getElementById('avatarPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;" />`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
