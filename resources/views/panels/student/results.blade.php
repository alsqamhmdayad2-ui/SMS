@extends('layouts.app')
@section('title', 'النتائج والدرجات - الطالب')

@section('content')

<x-page-header title="النتائج والدرجات">
    <x-slot:actions>
        <button class="btn btn-primary btn-sm" id="printCertificate" onclick="window.print()">
            <i class="fas fa-print ms-2"></i>طباعة الشهادة
        </button>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'النتائج والدرجات']
]" />

@php
    $subjects = collect($resultData['subjects'] ?? [])->filter(fn($s) => $s['is_published'])->all();
@endphp
<!-- سجل درجات المواد -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center data-table d-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-start ps-4">المادة</th>
                        <th>الدرجة النهائية</th>
                        <th>النسبة</th>
                        <th>التقدير</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subjectResult)
                        <tr>
                            <td class="text-start ps-4 fw-bold">{{ $subjectResult['subject']->name }}</td>
                            <td><span class="badge bg-success-subtle text-success fs-6">{{ $subjectResult['total_percentage'] }}/100</span></td>
                            <td><span class="badge {{ $subjectResult['is_passing'] ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">{{ $subjectResult['total_percentage'] }}%</span></td>
                            <td><span class="fw-bold {{ $subjectResult['is_passing'] ? 'text-success' : 'text-danger' }}">{{ $subjectResult['letter_grade'] ?? '-' }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info rounded-circle" onclick='showSubjectDetails(@json($subjectResult))' title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fs-4 mb-2 d-block opacity-50"></i>
                                لا توجد نتائج معتمدة حتى الآن
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Grade Details Modal -->
<div class="modal fade" id="gradeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title m-0 fw-bold"><i class="fas fa-chart-pie me-2 text-primary ms-2"></i>تفاصيل درجات المادة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailsBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function showSubjectDetails(subjectData) {
        const modal = new bootstrap.Modal(document.getElementById('gradeDetailsModal'));
        const modalBody = document.getElementById('modalDetailsBody');
        
        let html = '<ul class="list-group mb-3">';
        subjectData.components.forEach(comp => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt text-secondary ms-2"></i>${comp.name}</span>
                <span class="badge bg-primary rounded-pill">${comp.obtained}/${comp.total}</span>
            </li>`;
        });
        html += `<li class="list-group-item d-flex justify-content-between align-items-center bg-light fw-bold mt-2">
            <span><i class="fas fa-star text-warning ms-2"></i>المجموع الكلي (بالنسبة المئوية)</span>
            <span class="text-dark">${subjectData.total_percentage}%</span>
        </li></ul>`;
        
        modalBody.innerHTML = html;
        modal.show();
    }
</script>
@endpush
