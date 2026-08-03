@extends('layouts.app')
@section('title', 'نقل الطلاب')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-exchange-alt me-2 text-primary"></i>نقل الطلاب</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">نقل الطلاب</li>
            </ol>
        </nav>
    </div>
</div>


<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.transfers.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">بحث باسم الطالب أو الرقم</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="اكتب للبحث...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">تصفية بالصف</label>
                    <select name="class_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->grade->name ?? '' }} - {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">تصفية بالشعبة</label>
                    <select name="section_id" class="form-select">
                        <option value="">الكل</option>
                        {{-- In a real app, this should be dynamically loaded via JS based on class_id --}}
                        @foreach($classes as $c)
                            @foreach($c->sections as $s)
                                <option value="{{ $s->id }}" {{ request('section_id') == $s->id ? 'selected' : '' }}>
                                    {{ $c->name }} - {{ $s->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> بحث</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>قائمة الطلاب المتاحين للنقل</h5>
        <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
            العام النشط: {{ $activeYear->name }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">رقم الطالب</th>
                        <th>اسم الطالب</th>
                        <th>الصف الحالي</th>
                        <th>الشعبة الحالية</th>
                        <th class="text-center pe-4">إجراءات النقل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $enrollment->student->student_number }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:35px;height:35px;">
                                        {{ mb_substr($enrollment->student->first_name, 0, 1) }}
                                    </div>
                                    <span class="fw-bold">{{ $enrollment->student->first_name }} {{ $enrollment->student->family_name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $enrollment->schoolClass->name ?? 'غير محدد' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $enrollment->section->name ?? 'غير محدد' }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#internalTransferModal-{{ $enrollment->id }}">
                                    <i class="fas fa-exchange-alt me-1"></i> نقل داخلي
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#externalTransferModal-{{ $enrollment->id }}">
                                    <i class="fas fa-sign-out-alt me-1"></i> نقل خارجي
                                </button>

                                <!-- Internal Transfer Modal -->
                                <div class="modal fade text-start" id="internalTransferModal-{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold text-primary">نقل داخلي (تغيير شعبة)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.transfers.internal') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                                <div class="modal-body">
                                                    <p class="text-muted">نقل الطالب <strong>{{ $enrollment->student->first_name }} {{ $enrollment->student->family_name }}</strong> إلى شعبة أخرى في نفس العام.</p>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">الصف الجديد</label>
                                                        <select name="new_class_id"
                                                                class="form-select class-select"
                                                                data-enrollment="{{ $enrollment->id }}"
                                                                required>
                                                            <option value="">اختر الصف...</option>
                                                            @foreach($classes as $c)
                                                                <option value="{{ $c->id }}" {{ $enrollment->class_id == $c->id ? 'selected' : '' }}>
                                                                    {{ $c->grade->name ?? '' }} - {{ $c->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">الشعبة الجديدة</label>
                                                        <select name="new_section_id"
                                                                class="form-select section-select"
                                                                id="section-select-{{ $enrollment->id }}"
                                                                data-enrollment="{{ $enrollment->id }}"
                                                                required>
                                                            <option value="">اختر الشعبة...</option>
                                                            @foreach($enrollment->schoolClass->sections ?? [] as $s)
                                                                <option value="{{ $s->id }}" {{ $enrollment->section_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-check me-2"></i>تأكيد النقل</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- External Transfer Modal -->
                                <div class="modal fade text-start" id="externalTransferModal-{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold text-danger">نقل خارجي (خارج المدرسة)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.transfers.external') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                                <div class="modal-body">
                                                    <div class="alert alert-danger border-0">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        هل أنت متأكد أنك تريد نقل الطالب <strong>{{ $enrollment->student->first_name }} {{ $enrollment->student->family_name }}</strong> إلى مدرسة أخرى؟
                                                    </div>
                                                    <p class="text-muted small">هذا الإجراء سيقوم بإسقاط الطالب من قوائم الفصول الحالية وتغيير حالته إلى "منقول".</p>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-danger px-4"><i class="fas fa-sign-out-alt me-2"></i>تأكيد النقل الخارجي</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                <h5>لا يوجد طلاب نشطين متاحين للنقل.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
const sectionsMap = @json($classes->map(function($c) { return ['id' => $c->id, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()]; })->keyBy('id'));

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.class-select').forEach(function (classSelect) {
        const enrollmentId = classSelect.dataset.enrollment;
        const sectionSelect = document.getElementById('section-select-' + enrollmentId);

        classSelect.addEventListener('change', function () {
            const classId = this.value;
            sectionSelect.innerHTML = '<option value="">اختر الشعبة...</option>';

            if (classId && sectionsMap[classId]) {
                sectionsMap[classId].sections.forEach(function (section) {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.name;
                    sectionSelect.appendChild(option);
                });
            }
        });
    });
});
</script>
@endpush

@endsection
