@extends('layouts.app')
@section('title', 'الشهادات الرسمية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">الشهادات الرسمية والمعدل التراكمي (GPA)</h2>
                <p class="text-muted">توليد، نشر، وإدارة الشهادات الرسمية المعتمدة للطلاب.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-magic"></i> توليد لشعبة
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-body">
            <form action="{{ route('admin.report-cards.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">العام الدراسي</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">كل الأعوام...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الشعبة</label>
                    <select name="section_id" class="form-select">
                        <option value="">كل الشعب...</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter me-1"></i> تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @forelse($reportCards as $card)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-0">
                        <span class="badge bg-{{ $card->status->color() }} px-3 py-2 rounded-pill">
                            {{ $card->status->label() }}
                            @if($card->is_locked)
                                <i class="fas fa-lock text-warning ms-1" title="مقفلة"></i>
                            @endif
                        </span>
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> {{ $card->published_at ? $card->published_at->format('Y-m-d') : '-' }}</small>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary mb-1">{{ $card->student_name_snapshot }}</h5>
                        <p class="card-subtitle text-muted font-monospace small mb-3">{{ $card->student->student_id ?? 'بدون رقم' }}</p>
                        
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold"><i class="bi bi-calendar-event me-2"></i>الفترة:</span>
                                <span>{{ $card->report_period === 'semester' ? 'فصل دراسي' : 'سنوي' }} ({{ $card->academic_year_name_snapshot }})</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold"><i class="bi bi-diagram-3 me-2"></i>الشعبة:</span>
                                <span>{{ $card->section_name_snapshot }}</span>
                            </div>
                            <hr class="my-2 text-muted">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="fw-semibold"><i class="bi bi-calculator me-2"></i>المعدل التراكمي:</span>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-sms-primary">{{ $card->gpa }}</span><br>
                                    <span class="badge bg-info text-dark">{{ $card->total_percentage }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-top-0 pb-4 d-flex justify-content-center gap-2">
                        @if($card->status === App\Enums\ReportCardStatus::Generated)
                            <form action="{{ route('admin.report-cards.publish', $card->id) }}" method="POST" class="d-inline w-100">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 rounded-pill" onclick="return confirm('النشر سيقوم بقفل هذا السجل. هل تريد المتابعة؟')">
                                    <i class="fas fa-check me-1"></i> نشر واعتماد
                                </button>
                            </form>
                        @endif
                        
                        @if($card->status === App\Enums\ReportCardStatus::Published)
                            <a href="{{ route('admin.report-cards.pdf', $card->id) }}" class="btn btn-danger flex-grow-1 rounded-pill" target="_blank" title="تنزيل الشهادة PDF">
                                <i class="fas fa-file-pdf me-1"></i> تحميل PDF
                            </a>
                            
                            <form action="{{ route('admin.report-cards.revoke', $card->id) }}" method="POST" class="d-inline flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill" title="سحب المستند" onclick="return confirm('هل أنت متأكد من سحب هذا المستند الرسمي؟ سيؤدي هذا إلى إبطال رمز الاستجابة السريعة (QR).')">
                                    <i class="fas fa-ban me-1"></i> سحب
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 card shadow-sm border-0 rounded-4">
                    <x-shared.empty-state title="لا توجد شهادات" message="لم يتم توليد أي شهادات رسمية حتى الآن." icon="award" />
                </div>
            </div>
        @endforelse
    </div>

    @if($reportCards->hasPages())
        <div class="d-flex justify-content-center mb-4">
            {{ $reportCards->links() }}
        </div>
    @endif
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.report-cards.generate') }}" method="POST" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header bg-sms-primary text-white rounded-top-4">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i> توليد الشهادات الرسمية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info small rounded-3">
                    <i class="fas fa-info-circle me-1"></i> ملاحظة: يمكنك توليد الشهادات فقط إذا كانت <strong>جميع المواد</strong> للشعبة قد تم نشر نتائجها رسمياً.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">العام الدراسي</label>
                    <select name="academic_year_id" class="form-select" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الفصل الدراسي (اختياري)</label>
                    <select name="semester_id" class="form-select">
                        <option value="">سنة كاملة</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الشعبة</label>
                    <select name="section_id" class="form-select" required>
                        <option value="">اختر الشعبة...</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">فترة الشهادة</label>
                    <select name="report_period" class="form-select" required>
                        <option value="semester">فصل دراسي</option>
                        <option value="annual">سنوية نهائية</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> توليد الآن</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>
@endpush
