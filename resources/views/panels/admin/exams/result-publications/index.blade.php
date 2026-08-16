@extends('layouts.app')
@section('title', 'نشر النتائج')

@section('content')

<x-page-header title="نشر النتائج">
    <x-slot name="actions">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
            <i class="fas fa-bullhorn"></i> نشر نتائج جديدة
        </button>
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'نشر النتائج']
]" />

<div class="">

    <x-alerts />

    <!-- Existing Publications -->
    <div class="row g-4 mt-3">
        @forelse($publications as $pub)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-0">
                    <span class="badge {{ $pub->status === 'published' ? 'bg-success' : ($pub->status === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        @if($pub->status === 'published')
                            <i class="bi bi-check-circle me-1"></i> منشور
                        @elseif($pub->status === 'draft')
                            <i class="bi bi-pencil me-1"></i> مسودة
                        @else
                            <i class="bi bi-archive me-1"></i> مؤرشف
                        @endif
                    </span>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> {{ $pub->published_at ? $pub->published_at->format('Y-m-d H:i') : '-' }}</small>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary mb-1">{{ $pub->academicYear->name }}</h5>
                    <p class="card-subtitle text-muted small mb-3">{{ $pub->semester->name ?? 'سنة كاملة' }}</p>
                    
                    <div class="mb-3 p-3 bg-light rounded-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-mortarboard text-primary me-2"></i>
                            <span class="fw-semibold">الصف:</span> <span class="ms-2">{{ $pub->grade->name }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-layers text-info me-2"></i>
                            <span class="fw-semibold">الشعبة:</span> <span class="ms-2">{{ $pub->section->name }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-lock text-success me-2"></i>
                            <span class="fw-semibold">حالة الرصد:</span> 
                            <span class="ms-2 text-success">مقفلة بالكامل</span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center small text-muted">
                        <i class="bi bi-person-circle me-2"></i> الناشر: {{ $pub->publisher->name ?? 'النظام' }}
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0 pb-4 d-flex justify-content-end gap-2">
                    <form action="{{ route('admin.result-publications.update-status', $pub) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        @if($pub->status === 'published')
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="btn btn-outline-warning rounded-pill px-3" title="تحويل لمسودة">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> تراجع
                            </button>
                        @else
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-success rounded-pill px-3" title="نشر">
                                <i class="bi bi-check2-all me-1"></i> نشر
                            </button>
                        @endif
                    </form>
                    <form action="{{ route('admin.result-publications.destroy', $pub) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف سجل النشر هذا؟');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-circle" title="حذف"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <x-shared.empty-state icon="megaphone" title="لا يوجد سجلات نشر" message="لم يتم نشر أي نتائج حتى الآن." />
            </div>
        </div>
        @endforelse
    </div>
    
    @if($publications->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $publications->links() }}
    </div>
    @endif
</div>

<!-- Publish Modal -->
<x-shared.modal id="publishModal" title="<i class='bi bi-shield-check'></i> اعتماد ونشر نتائج فصلية" headerClass="bg-sms-primary text-white">
    <form action="{{ route('admin.result-publications.store') }}" method="POST">
        @csrf
        <x-slot:body>
            <div class="alert alert-warning py-2 small">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i> <strong>ملاحظة هامة:</strong> 
                اعتماد النتائج لشعبة معينة سيؤدي إلى <strong>قفل جميع المواد</strong> ومنع تعديل درجاتها، وستصبح النتائج والشهادات مرئية للطلاب وأولياء الأمور.
            </div>

            <div class="mb-3">
                <x-form.select name="publish_scope" id="publishScope" label="نطاق النشر" required="true" onchange="toggleScope()">
                    <option value="section">شعبة محددة</option>
                    <option value="grade">صف بالكامل (جميع شعبه)</option>
                    <option value="school">المدرسة بالكامل (جميع الصفوف والشعب)</option>
                </x-form.select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <x-form.select name="academic_year_id" label="العام الدراسي" required="true">
                        @foreach($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-6">
                    <x-form.select name="semester_id" label="الفصل الدراسي" required="true">
                        <option value="">اختر الفصل...</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>

            <div class="row g-2 mb-3" id="gradeSectionDiv">
                <div class="col-6" id="gradeDiv">
                    <x-form.select name="grade_id" id="gradeSelect" label="الصف الدراسي">
                        <option value="">اختر الصف...</option>
                        @foreach($grades as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-6" id="sectionDiv">
                    <label class="form-label fw-semibold text-sms-main">الشعبة المراد اعتمادها <span class="text-sms-danger">*</span></label>
                    <select name="section_id" id="sectionSelect" class="form-select">
                        <option value="">اختر الشعبة...</option>
                        @php $sections = \App\Models\Section::with('schoolClass')->get(); @endphp
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->schoolClass->name ?? '' }} - {{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-sms-main">ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="مثال: تم مراجعة جميع الدرجات واعتمادها نهائياً"></textarea>
            </div>

        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> تأكيد النشر</button>
        </x-slot:footer>
    </form>
</x-shared.modal>

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


@push('scripts')
<script>
    function toggleScope() {
        const scope = document.getElementById('publishScope').value;
        const gradeDiv = document.getElementById('gradeDiv');
        const sectionDiv = document.getElementById('sectionDiv');
        const gradeSectionDiv = document.getElementById('gradeSectionDiv');
        
        const gradeSelect = document.getElementById('gradeSelect');
        const sectionSelect = document.getElementById('sectionSelect');
        
        if (scope === 'school') {
            gradeSectionDiv.style.display = 'none';
            gradeSelect.removeAttribute('required');
            sectionSelect.removeAttribute('required');
        } else if (scope === 'grade') {
            gradeSectionDiv.style.display = 'flex';
            gradeDiv.style.display = 'block';
            sectionDiv.style.display = 'none';
            
            gradeSelect.setAttribute('required', 'required');
            sectionSelect.removeAttribute('required');
        } else {
            // section
            gradeSectionDiv.style.display = 'flex';
            gradeDiv.style.display = 'block';
            sectionDiv.style.display = 'block';
            
            gradeSelect.setAttribute('required', 'required');
            sectionSelect.setAttribute('required', 'required');
        }
    }
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleScope();
    });
</script>
@endpush
@endsection
