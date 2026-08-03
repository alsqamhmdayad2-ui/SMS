@extends('layouts.app')
@section('title', 'تفاصيل الطالب: ' . $student->full_name)

@section('content')

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2><i class="fas fa-user-graduate me-2" style="color:var(--secondary)"></i>بيانات الطالب</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">الطلاب</a></li>
                <li class="breadcrumb-item active">{{ $student->full_name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#transferStudentModal">
            <i class="fas fa-exchange-alt me-1"></i> نقل
        </button>
        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i> تعديل
        </a>
        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                <i class="fas fa-trash me-1"></i> حذف
            </button>
        </form>
    </div>
</div>

{{-- Profile Header --}}
<div class="card mb-4 mt-3">
    <div class="card-body">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            {{-- Avatar --}}
            <div style="width:90px;height:90px;border-radius:50%;overflow:hidden;flex-shrink:0;border:3px solid var(--border-color);background:var(--gradient-secondary);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff">
                @if($student->avatar)
                    <img src="{{ asset('storage/'.$student->avatar) }}" style="width:100%;height:100%;object-fit:cover;" />
                @else
                    {{ mb_strtoupper(mb_substr($student->first_name, 0, 1)) }}
                @endif
            </div>
            {{-- Name + Info --}}
            <div class="flex-grow-1">
                <h3 class="mb-1 fw-bold">{{ $student->full_name }}</h3>
                @if($student->english_name)
                    <p class="text-muted mb-1" style="font-family:sans-serif">{{ $student->english_name }}</p>
                @endif
                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size:.88rem">
                    <span><i class="fas fa-id-card me-1"></i>{{ $student->student_number ?? '—' }}</span>
                    <span><i class="fas fa-fingerprint me-1"></i>{{ $student->national_id }}</span>
                    @if($student->phone)
                        <span><i class="fas fa-phone me-1"></i>{{ $student->phone }}</span>
                    @endif
                    <span>
                        <i class="fas fa-graduation-cap me-1"></i>
                        {{ $student->grade?->name }} — {{ $student->schoolClass?->name }} ({{ $student->section?->name ?? '—' }})
                    </span>
                </div>
            </div>
            {{-- Status Badge --}}
            <span class="badge badge-success fs-6">نشط</span>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ─── البيانات الشخصية ─── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3><i class="fas fa-user me-2" style="color:var(--info)"></i>البيانات الشخصية</h3>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">الاسم الكامل</span>
                        <span class="info-value fw-semibold">{{ $student->full_name }}</span>
                    </li>
                    @if($student->english_name)
                    <li class="info-item">
                        <span class="info-label">الاسم بالإنجليزية</span>
                        <span class="info-value" style="font-family:sans-serif">{{ $student->english_name }}</span>
                    </li>
                    @endif
                    <li class="info-item">
                        <span class="info-label">رقم الهوية</span>
                        <span class="info-value"><strong>{{ $student->national_id }}</strong></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">رقم الهاتف</span>
                        <span class="info-value">{{ $student->phone ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">تاريخ الميلاد</span>
                        <span class="info-value">{{ optional($student->birth_date)->format('Y-m-d') ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">مكان الولادة</span>
                        <span class="info-value">{{ $student->place_of_birth ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الجنس</span>
                        <span class="info-value">
                            <span class="badge {{ $student->gender === 'Male' ? 'badge-info' : 'badge-danger' }}">
                                {{ $student->gender === 'Male' ? 'ذكر' : 'أنثى' }}
                            </span>
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الجنسية</span>
                        <span class="info-value">{{ $student->nationality ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الديانة</span>
                        <span class="info-value">{{ $student->religion === 'Muslim' ? 'مسلم' : ($student->religion === 'Christian' ? 'مسيحي' : '—') }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">فصيلة الدم</span>
                        <span class="info-value">{{ $student->blood_type ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الحالة الصحية</span>
                        <span class="info-value">{{ $student->health_status ?? 'سليم' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ─── بيانات ولي الأمر ─── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3><i class="fas fa-user-tie me-2" style="color:var(--purple)"></i>بيانات ولي الأمر</h3>
            </div>
            <div class="card-body">
                @if($student->parent)
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">الاسم الكامل</span>
                        <span class="info-value fw-semibold">{{ $student->parent->full_name }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">صلة القرابة</span>
                        <span class="info-value">
                            @php $gt = $student->parent->guardian_type @endphp
                            <span class="badge badge-info">{{ $gt === 'Father' ? 'الأب' : ($gt === 'Mother' ? 'الأم' : 'وصي') }}</span>
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">رقم الهوية</span>
                        <span class="info-value">{{ $student->parent->national_id ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">رقم الجوال</span>
                        <span class="info-value" style="direction:ltr">{{ $student->parent->phone_1 ?? '—' }}</span>
                    </li>
                    @if($student->parent->phone_2)
                    <li class="info-item">
                        <span class="info-label">رقم الجوال الثاني</span>
                        <span class="info-value" style="direction:ltr">{{ $student->parent->phone_2 }}</span>
                    </li>
                    @endif
                    <li class="info-item">
                        <span class="info-label">المهنة</span>
                        <span class="info-value">{{ $student->parent->occupation ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">جهة العمل</span>
                        <span class="info-value">{{ $student->parent->workplace ?? '—' }}</span>
                    </li>
                </ul>
                @else
                    <p class="text-muted text-center py-4"><i class="fas fa-user-slash fa-2x mb-2 d-block opacity-40"></i>لم يُحدد ولي أمر</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── عنوان السكن ─── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3><i class="fas fa-map-marker-alt me-2" style="color:var(--danger)"></i>عنوان السكن</h3>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">المحافظة</span>
                        <span class="info-value">{{ $student->governorate ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">المدينة / البلدة</span>
                        <span class="info-value">{{ $student->city ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">المنطقة الإدارية</span>
                        <span class="info-value">{{ $student->region ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الحي</span>
                        <span class="info-value">{{ $student->neighborhood ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الشارع</span>
                        <span class="info-value">{{ $student->street ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">أقرب معلم</span>
                        <span class="info-value">{{ $student->nearest_landmark ?? '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ─── البيانات الأكاديمية ─── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3><i class="fas fa-graduation-cap me-2" style="color:var(--accent)"></i>البيانات الأكاديمية</h3>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">رقم الطالب</span>
                        <span class="info-value"><strong>{{ $student->student_number ?? '—' }}</strong></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">المرحلة الدراسية</span>
                        <span class="info-value">{{ $student->grade?->name ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الصف</span>
                        <span class="info-value">{{ $student->schoolClass?->name ?? '—' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">الشعبة</span>
                        <span class="info-value"><span class="badge badge-info fs-6">{{ $student->section?->name ?? '—' }}</span></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">تاريخ الانضمام</span>
                        <span class="info-value">{{ $student->created_at?->format('Y-m-d') ?? '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- Transfer Modal --}}
<div class="modal fade" id="transferStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.students.transfer', $student->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">تغيير شعبة الطالب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        نقل الطالب الأكاديمي يقتصر حالياً على تغيير الشعبة داخل نفس الصف الدراسي.
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">المرحلة والصف (ثابت)</label>
                        <input type="text" class="form-control" value="{{ $student->grade?->name }} - {{ $student->schoolClass?->name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">اختر الشعبة الجديدة</label>
                        <select class="form-select" name="section_id" id="transfer_section_id" required>
                            <option value="">-- اختر الشعبة --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ $student->section_id == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt me-1"></i>حفظ وإتمام النقل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stageSelect   = document.getElementById('transfer_stage_id');
    const classSelect   = document.getElementById('transfer_class_id');
    const sectionSelect = document.getElementById('transfer_section_id');

    function filterClasses() {
        const stageId = stageSelect.value;
        Array.from(classSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!stageId || opt.dataset.stage === stageId) ? '' : 'none';
        });
        if (classSelect.options[classSelect.selectedIndex]?.style.display === 'none') classSelect.value = '';
        filterSections();
    }
    function filterSections() {
        const classId = classSelect.value;
        Array.from(sectionSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
        });
        if (sectionSelect.options[sectionSelect.selectedIndex]?.style.display === 'none') sectionSelect.value = '';
    }

    stageSelect.addEventListener('change', filterClasses);
    classSelect.addEventListener('change', filterSections);
    filterClasses();
});
</script>

@endsection
