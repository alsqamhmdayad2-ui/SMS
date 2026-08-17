@extends('layouts.app')
@section('title', 'مستنداتي')

@section('content')

<x-page-header title="المستندات الخاصة بي">
    <x-slot:actions>
        <span class="text-muted"><i class="fas fa-folder-open me-1"></i> أرشيف المستندات</span>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'المستندات']
]" />

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i> قائمة المستندات</h5>
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
                <p class="text-muted mb-0">لم يتم إضافة أي مستندات لملفك حتى الآن.</p>
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
                                    <a href="{{ route('student.documents.download', $document->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
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

@endsection
