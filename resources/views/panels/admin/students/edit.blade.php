@extends('layouts.app')
@section('title', 'تعديل بيانات الطالب')

@section('content')
@php
    $reqStageId = old('stage_id', $student->grade_id ?? '');
    $reqClassId = old('grade_id', $student->class_id ?? '');
    $reqSectionId = old('section_id', $student->section_id ?? '');
@endphp
<div class="page-header">
    <h2 id="pageTitle">تعديل بيانات الطالب</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">الطلاب</a></li>
            <li class="breadcrumb-item active" aria-current="page" id="breadcrumbItem">إضافة</li>
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

<form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @csrf
    <div class="row g-4">
        <!-- Personal Data -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle me-2"></i>البيانات الشخصية</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Avatar --}}
                        <div class="col-md-12 mb-2">
                            <div class="d-flex align-items-center gap-4">
                                <div class="text-center">
                                    <div id="avatarPreview" style="width:90px;height:90px;border-radius:50%;background:var(--gradient-secondary);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;overflow:hidden;border:3px solid var(--border-color)">
                                        @if($student->avatar)
                                            <img src="{{ asset('storage/'.$student->avatar) }}" style="width:100%;height:100%;object-fit:cover" />
                                        @else
                                            {{ mb_strtoupper(mb_substr($student->first_name,0,1)) }}
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">صورة الطالب</small>
                                </div>
                                <div>
                                    <label class="form-label">تغيير الصورة الشخصية (اختياري)</label>
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
                                   name="first_name" value="{{ old('first_name', $student->first_name) }}" required />
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الأب</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ old('father_name', $student->father_name) }}" required />
                            @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم الجد</label>
                            <input type="text" class="form-control @error('grandfather_name') is-invalid @enderror"
                                   name="grandfather_name" value="{{ old('grandfather_name', $student->grandfather_name) }}" required />
                            @error('grandfather_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">اسم العائلة</label>
                            <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                   name="family_name" value="{{ old('family_name', $student->family_name) }}" required />
                            @error('family_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- الاسم بالإنجليزية --}}
                        <div class="col-md-3">
                            <label class="form-label">الاسم بالإنجليزية</label>
                            <input type="text" class="form-control" name="english_name"
                                   value="{{ old('english_name', $student->english_name) }}" placeholder="Full name in English" />
                        </div>

                        {{-- رقم الهوية --}}
                        <div class="col-md-3">
                            <label class="form-label required">رقم الهوية / جواز السفر</label>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                   name="national_id" value="{{ old('national_id', $student->national_id) }}" required />
                            @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="col-md-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control" name="phone"
                                   value="{{ old('phone', $student->phone) }}" placeholder="05xxxxxxxx" />
                        </div>

                        {{-- تاريخ الميلاد --}}
                        <div class="col-md-3">
                            <label class="form-label required">تاريخ الميلاد</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                   name="birth_date" value="{{ old('birth_date', optional($student->birth_date)->format('Y-m-d')) }}" required />
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- مكان الولادة --}}
                        <div class="col-md-3">
                            <label class="form-label">مكان الولادة</label>
                            <input type="text" class="form-control" name="place_of_birth"
                                   value="{{ old('place_of_birth', $student->place_of_birth) }}" />
                        </div>

                        {{-- الجنس --}}
                        <div class="col-md-3">
                            <label class="form-label required">الجنس</label>
                            <select class="form-select" name="gender" required>
                                <option value="">اختر الجنس</option>
                                <option value="Male"   {{ old('gender', $student->gender) == 'Male'   ? 'selected' : '' }}>ذكر</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>

                        {{-- الجنسية --}}
                        <div class="col-md-3">
                            <label class="form-label required">الجنسية</label>
                            <input type="text" class="form-control" name="nationality"
                                   value="{{ old('nationality', $student->nationality ?? 'فلسطيني') }}" required />
                        </div>

                        {{-- فصيلة الدم --}}
                        <div class="col-md-3">
                            <label class="form-label">فصيلة الدم</label>
                            <select class="form-select" name="blood_type">
                                <option value="">اختر الفصيلة</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type', $student->blood_type) == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- الديانة --}}
                        <div class="col-md-3">
                            <label class="form-label">الديانة</label>
                            <select class="form-select" name="religion">
                                <option value="Muslim"    {{ old('religion', $student->religion) == 'Muslim'    ? 'selected' : '' }}>مسلم</option>
                                <option value="Christian" {{ old('religion', $student->religion) == 'Christian' ? 'selected' : '' }}>مسيحي</option>
                            </select>
                        </div>

                        {{-- الحالة الصحية --}}
                        <div class="col-md-3">
                            <label class="form-label">الحالة الصحية</label>
                            <input type="text" class="form-control" name="health_status"
                                   value="{{ old('health_status', $student->health_status) }}" placeholder="سليم، يعاني من..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Data -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-tie me-2"></i>بيانات ولي الأمر</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold text-primary">
                                <i class="fas fa-search me-1"></i> ابحث عن ولي أمر مسجل مسبقاً (اختياري)
                            </label>
                            <select class="form-select searchable-select" name="parent_id" data-placeholder="-- اختر ولي الأمر --" style="width: 100%;">
                                <option value="">اكتب للبحث، أو اترك فارغاً لإنشاء ولي أمر جديد...</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->full_name }} ({{ $parent->national_id }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">إذا اخترت ولي أمر من القائمة، تُتجاهل الحقول أدناه تلقائياً.</small>
                        </div>

                        <div class="col-12"><hr class="my-1"><p class="text-muted mb-2 small">أو عدّل بيانات ولي الأمر الحالي:</p></div>

                        <div class="col-md-3">
                            <label class="form-label">صلة القرابة</label>
                            <select class="form-select" name="guardian_type">
                                <option value="Father"   {{ old('guardian_type', $student->parent?->guardian_type) == 'Father'   ? 'selected' : '' }}>الأب</option>
                                <option value="Mother"   {{ old('guardian_type', $student->parent?->guardian_type) == 'Mother'   ? 'selected' : '' }}>الأم</option>
                                <option value="Guardian" {{ old('guardian_type', $student->parent?->guardian_type) == 'Guardian' ? 'selected' : '' }}>وصي قانوني</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الاسم الكامل لولي الأمر</label>
                            <input type="text" class="form-control" name="parent_full_name"
                                   value="{{ old('parent_full_name', $student->parent?->full_name) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">رقم هوية ولي الأمر</label>
                            <input type="text" class="form-control" name="parent_national_id"
                                   value="{{ old('parent_national_id', $student->parent?->national_id) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">رقم الجوال</label>
                            <input type="text" class="form-control" name="parent_phone_1"
                                   value="{{ old('parent_phone_1', $student->parent?->phone_1) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">رقم الجوال الثاني</label>
                            <input type="text" class="form-control" name="parent_phone_2"
                                   value="{{ old('parent_phone_2', $student->parent?->phone_2) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المهنة</label>
                            <input type="text" class="form-control" name="parent_occupation"
                                   value="{{ old('parent_occupation', $student->parent?->occupation) }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Data -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-map-marker-alt me-2"></i>عنوان السكن</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label required">المحافظة</label>
                            <select class="form-select" name="governorate" required>
                                <option value="">اختر المحافظة</option>
                                @foreach(['شمال غزة', 'غزة', 'الوسطى', 'خانيونس', 'رفح'] as $gov)
                                    <option value="{{ $gov }}" {{ old('governorate', $student->governorate) == $gov ? 'selected' : '' }}>{{ $gov }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">المدينة / البلدة</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city', $student->city) }}" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المنطقة / الحي</label>
                            <input type="text" class="form-control" name="neighborhood" value="{{ old('neighborhood', $student->neighborhood) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المنطقة الإدارية</label>
                            <input type="text" class="form-control" name="region" value="{{ old('region', $student->region) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشارع</label>
                            <input type="text" class="form-control" name="street" value="{{ old('street', $student->street) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">أقرب معلم / نقطة بارزة</label>
                            <input type="text" class="form-control" name="nearest_landmark" value="{{ old('nearest_landmark', $student->nearest_landmark) }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Data -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap me-2"></i>البيانات الأكاديمية</h3>
                </div>
                <div class="card-body">
                    @php
                        if (request('section_id') && !old('section_id')) {
                            $selectedSection = \App\Models\Section::with('schoolClass.grade')->find(request('section_id'));
                            if ($selectedSection) {
                                $reqClassId = $selectedSection->class_id;
                                $reqStageId = $selectedSection->schoolClass->grade_id ?? null;
                            }
                        }
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label required">المرحلة الدراسية</label>
                            <select class="form-select" name="stage_id" id="stage_id" required>
                                <option value="">اختر المرحلة</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ $reqStageId == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">الصف</label>
                            <select class="form-select" name="grade_id" id="grade_id" required {{ $reqStageId ? '' : 'disabled' }}>
                                <option value="">اختر الصف</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">الشعبة</label>
                            <select class="form-select" name="section_id" id="section_id" required {{ $reqClassId ? '' : 'disabled' }}>
                                <option value="">اختر الشعبة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">تاريخ الالتحاق</label>
                            <input type="date" class="form-control" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">نوع التسجيل</label>
                            <select class="form-select" name="registration_type" required>
                                <option value="New" {{ old('registration_type') == 'New' ? 'selected' : '' }}>طالب جديد</option>
                                <option value="Transferred" {{ old('registration_type') == 'Transferred' ? 'selected' : '' }}>منقول</option>
                                <option value="Re-enrolled" {{ old('registration_type') == 'Re-enrolled' ? 'selected' : '' }}>إعادة قيد</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المدرسة السابقة (إن وجدت)</label>
                            <input type="text" class="form-control" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">سبب النقل</label>
                            <input type="text" class="form-control" name="transfer_reason" value="{{ old('transfer_reason', $student->transfer_reason) }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Data -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt me-2"></i>الوثائق المطلوبة</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">شهادة الميلاد <small class="text-danger">(إلزامية)</small></label>
                            <input type="file" class="form-control" name="doc_birth_certificate" accept=".pdf,image/*" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">صورة هوية ولي الأمر <small class="text-danger">(إلزامية)</small></label>
                            <input type="file" class="form-control" name="doc_parent_id" accept=".pdf,image/*" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">إثبات السكن <small class="text-danger">(إلزامية)</small></label>
                            <input type="file" class="form-control" name="doc_address_proof" accept=".pdf,image/*" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">شهادة انتقال</label>
                            <input type="file" class="form-control" name="doc_transfer" accept=".pdf,image/*" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">آخر كشف علامات</label>
                            <input type="file" class="form-control" name="doc_transcript" accept=".pdf,image/*" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">صورة شخصية للطالب</label>
                            <input type="file" class="form-control" name="doc_avatar" accept="image/*" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Credentials Info -->
        <div class="col-12 mt-2">
            <div class="alert border-start border-4 border-info bg-info bg-opacity-10 rounded-3 mb-2" role="alert">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-key text-info fs-5"></i>
                    <h6 class="fw-bold mb-0 text-info">بيانات الدخول للنظام (تُولَّد تلقائياً)</h6>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 small">
                            <i class="fas fa-id-card text-muted"></i>
                            <span class="fw-semibold">اسم المستخدم (للدخول):</span>
                            <code class="bg-white rounded px-2 py-1 border">رقم الهوية المُدخل</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 small">
                            <i class="fas fa-lock text-muted"></i>
                            <span class="fw-semibold">كلمة المرور الافتراضية:</span>
                            <code class="bg-white rounded px-2 py-1 border">رقم الهوية المُدخل</code>
                        </div>
                    </div>
                </div>
                <p class="mb-0 mt-2 small text-muted"><i class="fas fa-info-circle me-1"></i>يمكن للطالب تغيير كلمة المرور بعد أول تسجيل دخول.</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 mt-4 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary px-5">
                    <i class="fas fa-save me-1"></i> حفظ البيانات
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
                    'id' => $c->id,
                    'name' => $c->name,
                    'sections' => $c->sections->map(function($s) {
                        return ['id' => $s->id, 'name' => $s->name];
                    })->values()
                ];
            })->values()
        ];
    })->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradesData = @json($gradesJsonData);

    const stageSelect = document.getElementById('stage_id');
    const classSelect = document.getElementById('grade_id');
    const sectionSelect = document.getElementById('section_id');

    const oldStageId = '{{ old('stage_id', $student->grade_id) }}';
    const oldClassId = '{{ old('class_id', $student->class_id) }}';
    const oldSectionId = '{{ old('section_id', $student->section_id) }}';

    function updateClasses() {
        const stageId = stageSelect.value || oldStageId;
        classSelect.innerHTML = '<option value="">اختر الصف</option>';
        sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';
        sectionSelect.disabled = true;

        if (!stageId) {
            classSelect.disabled = true;
            return;
        }

        classSelect.disabled = false;
        const selectedStage = gradesData.find(g => g.id == stageId);
        
        if (selectedStage && selectedStage.classes) {
            selectedStage.classes.forEach(cls => {
                const isSelected = oldClassId == cls.id ? 'selected' : '';
                classSelect.innerHTML += `<option value="${cls.id}" ${isSelected}>${cls.name}</option>`;
            });
        }
        
        if (classSelect.value) {
            updateSections();
        }
    }

    function updateSections() {
        const stageId = stageSelect.value || oldStageId;
        const classId = classSelect.value;
        sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';

        if (!classId) {
            sectionSelect.disabled = true;
            return;
        }

        sectionSelect.disabled = false;
        const selectedStage = gradesData.find(g => g.id == stageId);
        if (selectedStage) {
            const selectedClass = selectedStage.classes.find(c => c.id == classId);
            if (selectedClass && selectedClass.sections) {
                selectedClass.sections.forEach(sec => {
                    const isSelected = oldSectionId == sec.id ? 'selected' : '';
                    sectionSelect.innerHTML += `<option value="${sec.id}" ${isSelected}>${sec.name}</option>`;
                });
            }
        }
    }

    stageSelect.addEventListener('change', updateClasses);
    classSelect.addEventListener('change', updateSections);

    if (stageSelect.value || oldStageId) {
        if (!stageSelect.value && oldStageId) stageSelect.value = oldStageId;
        updateClasses();
    }
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
