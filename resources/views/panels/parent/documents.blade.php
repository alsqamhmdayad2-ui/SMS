@extends('layouts.app')
@section('title', 'مستندات الأبناء - ولي الأمر')

@section('content')

<x-page-header title="المستندات">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'المستندات']
]" />

<div class="card mb-4 shadow-sm border-0 slide-up">
    <div class="card-body">
        <form action="{{ route('parent.documents') }}" method="GET" class="row align-items-end" id="childSelectForm">
            <div class="col-md-6">
                <label for="student_id" class="form-label fw-bold"><i class="fas fa-child text-primary me-2"></i>اختر الابن</label>
                <select name="student_id" id="student_id" class="form-select form-select-lg" onchange="document.getElementById('childSelectForm').submit()">
                    @forelse($children as $child)
                        <option value="{{ $child->id }}" {{ $selectedChild && $selectedChild->id == $child->id ? 'selected' : '' }}>
                            {{ $child->name }}
                        </option>
                    @empty
                        <option value="">لا يوجد أبناء مضافين</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>
</div>

@if($selectedChild)
<div class="card shadow-sm border-0 slide-up" style="animation-delay: 0.1s;">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i> مستندات: {{ $selectedChild->name }}</h5>
    </div>
    <div class="card-body p-0">
        @if(session('error'))
            <div class="alert alert-danger m-3">
                {{ session('error') }}
            </div>
        @endif

        @if($documents->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-folder-minus text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted fw-bold">لا توجد مستندات</h5>
                <p class="text-muted mb-0">لم يتم إضافة أي مستندات لملف هذا الابن حتى الآن.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">نوع المستند</th>
                            <th>حالة التحقق</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </div>
                                        <span class="fw-bold">{{ $document->document_type }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($document->is_verified)
                                        <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> تم التحقق</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i> قيد المراجعة</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted">{{ $document->created_at->format('Y/m/d') }}</div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('parent.documents.download', $document->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-download me-1"></i> تحميل
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@else
<x-empty-state message="لم يتم العثور على الابن المطلوب أو لا يوجد أبناء مسجلين" icon="fas fa-child" />
@endif

@endsection
