@extends('layouts.app')
@section('title', 'إعدادات النظام')

@section('content')

<div class="page-header">
    <h2><i class="fas fa-cogs me-2"></i>إعدادات النظام</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item active">إعدادات النظام</li>
        </ol>
    </nav>
</div>


@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ─── هوية المدرسة ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-school me-2" style="color:var(--primary)"></i>هوية المدرسة</h3>
                </div>
                <div class="card-body">

                    {{-- الشعارات --}}
                    <div class="row g-4 mb-4">
                        {{-- الشعار الرئيسي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الشعار الرئيسي (Logo)</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div id="logoPreview" style="width:80px;height:80px;border:2px dashed var(--border-color);border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8f9fa">
                                    @if($settings->logo)
                                        <img src="{{ asset('storage/'.$settings->logo) }}" style="width:100%;height:100%;object-fit:contain" />
                                    @else
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control" name="logo" accept="image/*"
                                           onchange="previewImage(this,'logoPreview')" />
                                    <small class="text-muted">PNG أو SVG — شفاف مُفضَّل</small>
                                </div>
                            </div>
                        </div>
                        {{-- الشعار الأكاديمي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الشعار الأكاديمي (للشهادات والتقارير)</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div id="academicLogoPreview" style="width:80px;height:80px;border:2px dashed var(--border-color);border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8f9fa">
                                    @if($settings->academic_logo)
                                        <img src="{{ asset('storage/'.$settings->academic_logo) }}" style="width:100%;height:100%;object-fit:contain" />
                                    @else
                                        <i class="fas fa-certificate fa-2x text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control" name="academic_logo" accept="image/*"
                                           onchange="previewImage(this,'academicLogoPreview')" />
                                    <small class="text-muted">يُستخدم في طباعة الشهادات</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- كود المدرسة --}}
                        <div class="col-md-3">
                            <label class="form-label">كود/رقم المدرسة</label>
                            <input type="text" class="form-control" name="school_code"
                                   value="{{ old('school_code', $settings->school_code) }}"
                                   placeholder="مثال: SCH-001" />
                        </div>

                        {{-- الاسم الكامل --}}
                        <div class="col-md-5">
                            <label class="form-label required">اسم المدرسة بالعربية</label>
                            <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                   name="school_name" value="{{ old('school_name', $settings->school_name) }}"
                                   placeholder="مدرسة الأمل الأساسية" required />
                            @error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- الاسم المختصر --}}
                        <div class="col-md-4">
                            <label class="form-label">الاسم المختصر</label>
                            <input type="text" class="form-control" name="school_short_name"
                                   value="{{ old('school_short_name', $settings->school_short_name) }}"
                                   placeholder="الأمل" />
                            <small class="text-muted">يظهر في الـ Sidebar والتقارير</small>
                        </div>

                        {{-- الاسم بالإنجليزية --}}
                        <div class="col-md-6">
                            <label class="form-label">اسم المدرسة بالإنجليزية</label>
                            <input type="text" class="form-control" name="school_name_en"
                                   value="{{ old('school_name_en', $settings->school_name_en) }}"
                                   placeholder="Al-Amal Elementary School" />
                        </div>

                        {{-- المدير --}}
                        <div class="col-md-6">
                            <label class="form-label">اسم المدير</label>
                            <input type="text" class="form-control" name="principal_name"
                                   value="{{ old('principal_name', $settings->principal_name) }}"
                                   placeholder="أ. محمد أحمد" />
                        </div>

                        {{-- توقيع المدير --}}
                        <div class="col-md-12">
                            <label class="form-label">صورة توقيع المدير <small class="text-muted">(تُستخدم في طباعة الشهادات)</small></label>
                            <div class="d-flex align-items-center gap-3">
                                <div id="signaturePreview" style="width:180px;height:60px;border:1px dashed var(--border-color);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fff">
                                    @if($settings->principal_signature)
                                        <img src="{{ asset('storage/'.$settings->principal_signature) }}" style="max-width:100%;max-height:100%;object-fit:contain" />
                                    @else
                                        <small class="text-muted">لا يوجد توقيع</small>
                                    @endif
                                </div>
                                <input type="file" class="form-control" name="principal_signature" accept="image/*"
                                       onchange="previewImage(this,'signaturePreview')" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── بيانات التواصل والموقع ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-map-marker-alt me-2" style="color:var(--danger)"></i>بيانات التواصل والموقع</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control" name="phone"
                                   value="{{ old('phone', $settings->phone) }}" placeholder="+970-8-xxxxxxx" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $settings->email) }}"
                                   placeholder="info@school.edu" />
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الموقع الإلكتروني</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   name="website" value="{{ old('website', $settings->website) }}"
                                   placeholder="https://school.edu.ps" />
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الدولة</label>
                            <input type="text" class="form-control" name="country"
                                   value="{{ old('country', $settings->country ?? 'فلسطين') }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">المدينة</label>
                            <input type="text" class="form-control" name="city"
                                   value="{{ old('city', $settings->city) }}" placeholder="غزة" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الرمز البريدي</label>
                            <input type="text" class="form-control" name="postal_code"
                                   value="{{ old('postal_code', $settings->postal_code) }}" />
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">العنوان التفصيلي</label>
                            <textarea class="form-control" name="address" rows="2"
                                      placeholder="اسم الشارع، الحي، المدينة">{{ old('address', $settings->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── الإعدادات الأكاديمية ─── --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap me-2" style="color:var(--accent)"></i>الإعدادات الأكاديمية والنظامية</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- النظام الدراسي --}}
                        <div class="col-md-4">
                            <label class="form-label">النظام الأكاديمي <small class="text-muted">(تقسيم العام الدراسي)</small></label>
                            <select class="form-select" name="academic_system">
                                <option value="">-- اختر النظام --</option>
                                <option value="semester"   {{ old('academic_system', $settings->academic_system) == 'semester'   ? 'selected' : '' }}>فصلي (Semester) — فصلان في السنة</option>
                                <option value="trimester"  {{ old('academic_system', $settings->academic_system) == 'trimester'  ? 'selected' : '' }}>ثلاثي (Trimester) — ثلاثة فصول</option>
                                <option value="annual"     {{ old('academic_system', $settings->academic_system) == 'annual'     ? 'selected' : '' }}>سنوي (Annual) — تقييم سنوي واحد</option>
                                <option value="quarterly"  {{ old('academic_system', $settings->academic_system) == 'quarterly'  ? 'selected' : '' }}>ربع سنوي (Quarterly) — أربعة فصول</option>
                            </select>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                يحدد كيفية تقسيم السنة الدراسية وعدد الفصول
                            </small>
                        </div>

                        {{-- نظام التقييم --}}
                        <div class="col-md-4">
                            <label class="form-label">نظام التقييم والدرجات</label>
                            <select class="form-select" name="grading_system">
                                <option value="">-- اختر النظام --</option>
                                <option value="percentage"  {{ old('grading_system', $settings->grading_system) == 'percentage'  ? 'selected' : '' }}>نسبة مئوية (0–100)</option>
                                <option value="letter"      {{ old('grading_system', $settings->grading_system) == 'letter'      ? 'selected' : '' }}>حروف (A, B, C, D, F)</option>
                                <option value="gpa"         {{ old('grading_system', $settings->grading_system) == 'gpa'         ? 'selected' : '' }}>معدل تراكمي (GPA 0–4)</option>
                                <option value="pass_fail"   {{ old('grading_system', $settings->grading_system) == 'pass_fail'   ? 'selected' : '' }}>ناجح / راسب فقط</option>
                            </select>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                يؤثر على كيفية عرض الدرجات في التقارير والشهادات
                            </small>
                        </div>

                        {{-- المنطقة الزمنية --}}
                        <div class="col-md-4">
                            <label class="form-label">المنطقة الزمنية (Timezone)</label>
                            <select class="form-select" name="timezone">
                                <option value="Asia/Gaza"       {{ old('timezone', $settings->timezone) == 'Asia/Gaza'       ? 'selected' : '' }}>آسيا / غزة (GMT+2/+3)</option>
                                <option value="Asia/Jerusalem"  {{ old('timezone', $settings->timezone) == 'Asia/Jerusalem'  ? 'selected' : '' }}>آسيا / القدس</option>
                                <option value="Asia/Riyadh"     {{ old('timezone', $settings->timezone) == 'Asia/Riyadh'     ? 'selected' : '' }}>آسيا / الرياض (GMT+3)</option>
                                <option value="Asia/Amman"      {{ old('timezone', $settings->timezone) == 'Asia/Amman'      ? 'selected' : '' }}>آسيا / عمّان</option>
                                <option value="Africa/Cairo"    {{ old('timezone', $settings->timezone) == 'Africa/Cairo'    ? 'selected' : '' }}>أفريقيا / القاهرة</option>
                                <option value="UTC"             {{ old('timezone', $settings->timezone) == 'UTC'             ? 'selected' : '' }}>UTC (توقيت عالمي)</option>
                            </select>
                        </div>

                        {{-- العملة --}}
                        <div class="col-md-4">
                            <label class="form-label">العملة</label>
                            <select class="form-select" name="currency">
                                <option value="ILS" {{ old('currency', $settings->currency) == 'ILS' ? 'selected' : '' }}>₪ شيكل إسرائيلي (ILS)</option>
                                <option value="USD" {{ old('currency', $settings->currency) == 'USD' ? 'selected' : '' }}>$ دولار أمريكي (USD)</option>
                                <option value="JOD" {{ old('currency', $settings->currency) == 'JOD' ? 'selected' : '' }}>د.أ دينار أردني (JOD)</option>
                                <option value="EGP" {{ old('currency', $settings->currency) == 'EGP' ? 'selected' : '' }}>ج.م جنيه مصري (EGP)</option>
                            </select>
                        </div>

                        {{-- تذييل التقارير --}}
                        <div class="col-md-8">
                            <label class="form-label">نص تذييل التقارير والشهادات</label>
                            <textarea class="form-control" name="report_footer" rows="2"
                                      placeholder="مثال: هذه الشهادة صادرة عن مدرسة الأمل بموجب...">{{ old('report_footer', $settings->report_footer) }}</textarea>
                            <small class="text-muted">يظهر في أسفل كل تقرير أو شهادة مطبوعة</small>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ─── زر الحفظ ─── --}}
        <div class="col-12 mb-5">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="fas fa-save me-2"></i>حفظ الإعدادات
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:contain;" />`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
