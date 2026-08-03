@extends('layouts.app')
@section('title', 'إدارة الترقيات والتراجع')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-history me-2 text-danger"></i>إدارة الترقيات (التراجع)</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">الترقية</a></li>
                <li class="breadcrumb-item active">إدارة الترقيات</li>
            </ol>
        </nav>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-muted"></i>البحث عن الطلاب المُرَقين</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.promotions.management') }}" method="GET">
            <div class="row g-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-danger">عام الترقية (إلزامي):</label>
                    <select name="year_id" class="form-select border-danger" required>
                        <option value="">اختر العام...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->status ? '(نشط)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold" title="مثال: إذا رقيتهم للصف الثاني اختر الصف الثاني">الصف الذي نُقلوا إليه:</label>
                    <select name="class_id" class="form-select">
                        <option value="">جميع الصفوف</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->grade->name ?? '' }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">تصفية حسب التاريخ (اختياري):</label>
                    <input type="date" name="date" class="form-select" value="{{ $date ?? '' }}">
                </div>
                
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
                        <i class="fas fa-search me-2"></i>عرض السجلات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($yearId)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>الطلاب المرقين أو المنقولين للعام المحدد</h5>
            
            @if($enrollments->isNotEmpty())
            <form action="{{ route('admin.promotions.undo-all') }}" method="POST" id="undo-all-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="year_id" value="{{ $yearId }}">
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="btn-undo-all">
                    <i class="fas fa-undo-alt me-2"></i>التراجع عن الترقية لجميع السجلات المعروضة ({{ $enrollments->count() }})
                </button>
            </form>
            @endif
        </div>
        
        <div class="card-body p-0">
            @if($enrollments->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold">لا يوجد سجلات ترقية</h5>
                    <p class="text-muted">لم يتم ترقية أي طلاب إلى هذا العام الأكاديمي حتى الآن.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4">الطالب</th>
                                <th>رقم الطالب</th>
                                <th>الصف الحالي الجديد</th>
                                <th>تاريخ العملية</th>
                                <th class="text-end pe-4">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $enrollment)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $enrollment->student->full_name ?? 'غير معروف' }}</td>
                                    <td><span class="badge bg-light text-dark font-monospace border">{{ $enrollment->student->student_number ?? '—' }}</span></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $enrollment->schoolClass->name ?? '—' }}</span>
                                        @if($enrollment->section)
                                            - <span class="text-muted small">شعبة {{ $enrollment->section->name }}</span>
                                        @else
                                            <span class="text-danger small">(لم يوزع على شعبة)</span>
                                        @endif
                                    </td>
                                    <td>{{ $enrollment->registration_date ? \Carbon\Carbon::parse($enrollment->registration_date)->format('Y-m-d') : '—' }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.promotions.undo', $enrollment->id) }}" method="POST" class="undo-form d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-undo" title="تراجع عن الترقية">
                                                <i class="fas fa-undo me-1"></i> تراجع عن الترقية
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const undoBtns = document.querySelectorAll('.btn-undo');
        undoBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('هل أنت متأكد من إلغاء ترقية هذا الطالب؟ سيتم إرجاعه لصفه وشعبته في العام الماضي، وحذف سجل ترقيته الجديد نهائياً.')) {
                    this.closest('form').submit();
                }
            });
        });
        
        const btnUndoAll = document.getElementById('btn-undo-all');
        if(btnUndoAll) {
            btnUndoAll.addEventListener('click', function() {
                if(confirm('تحذير: هل أنت متأكد من التراجع عن ترقية جميع الطلاب المعروضين في هذا الجدول دفعة واحدة؟ لا يمكن التراجع عن هذه الخطوة!')) {
                    document.getElementById('undo-all-form').submit();
                }
            });
        }
    });
</script>
@endpush
