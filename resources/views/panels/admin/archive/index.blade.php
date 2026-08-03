@extends('layouts.app')
@section('title', 'سلة المهملات والأرشيف')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-trash-restore me-2 text-danger"></i>سلة المهملات (الأرشيف)</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الأرشيف</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <!-- Tabs Header -->
    <div class="card-header bg-white p-0 border-bottom">
        <div class="d-flex flex-nowrap overflow-auto hide-scrollbar">
            @php
                $tabs = [
                    'students' => ['icon' => 'user-graduate', 'label' => 'الطلاب'],
                    'teachers' => ['icon' => 'chalkboard-teacher', 'label' => 'المعلمين'],
                    'parents'  => ['icon' => 'users', 'label' => 'أولياء الأمور'],
                    'subjects' => ['icon' => 'book', 'label' => 'المواد الدراسية'],
                    'sections' => ['icon' => 'layer-group', 'label' => 'الشعب'],
                    'classes'  => ['icon' => 'door-open', 'label' => 'الصفوف'],
                    'grades'   => ['icon' => 'sitemap', 'label' => 'المراحل'],
                ];
            @endphp
            
            <ul class="nav nav-tabs nav-tabs-custom border-0 w-100 flex-nowrap" style="margin-bottom: -1px;">
                @foreach($tabs as $key => $details)
                    <li class="nav-item">
                        <a class="nav-link border-0 fw-semibold py-3 px-4 text-nowrap {{ $tab == $key ? 'active text-primary border-bottom border-primary border-3 bg-light' : 'text-muted' }}" 
                           href="{{ route('admin.archive.index', ['tab' => $key]) }}">
                            <i class="fas fa-{{ $details['icon'] }} me-2"></i>{{ $details['label'] }}
                            @if($counts[$key] > 0)
                                <span class="badge bg-danger ms-1 rounded-pill">{{ $counts[$key] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Toolbar: Search & Empty Trash -->
    <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
        <form action="{{ route('admin.archive.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="ابحث في المحذوفات..." value="{{ $search }}">
                @if($search)
                    <a href="{{ route('admin.archive.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary border-start-0" title="مسح البحث">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <button type="submit" class="btn btn-primary px-4">بحث</button>
            </div>
        </form>

        @if($counts[$tab] > 0)
            <form action="{{ route('admin.archive.empty', ['type' => $tab]) }}" method="POST" class="empty-trash-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger px-4 rounded-pill shadow-sm btn-empty-trash">
                    <i class="fas fa-dumpster-fire me-2"></i>تفريغ السلة ({{ $counts[$tab] }})
                </button>
            </form>
        @endif
    </div>

    <!-- Data Table -->
    <div class="card-body p-0">
        @if($data->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-box-open fa-2x text-muted opacity-50"></i>
                </div>
                @if($search)
                    <h5 class="text-muted fw-semibold mb-1">لا توجد نتائج بحث</h5>
                    <p class="text-muted small">لم يتم العثور على أية عناصر تطابق بحثك في قسم {{ $tabs[$tab]['label'] }}.</p>
                    <a href="{{ route('admin.archive.index', ['tab' => $tab]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">عرض كل المحذوفات</a>
                @else
                    <h5 class="text-muted fw-semibold mb-1">السلة فارغة</h5>
                    <p class="text-muted small">لا توجد عناصر محذوفة في قسم {{ $tabs[$tab]['label'] }}.</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">الاسم / العنوان</th>
                            
                            @if($tab == 'students')
                                <th>رقم الطالب</th>
                                <th>الصف والشعبة</th>
                            @elseif($tab == 'teachers')
                                <th>الهوية</th>
                                <th>التخصص</th>
                            @elseif($tab == 'parents')
                                <th>الهوية</th>
                                <th>الهاتف</th>
                            @elseif($tab == 'subjects')
                                <th>الكود</th>
                            @elseif($tab == 'sections')
                                <th>الصف التابع له</th>
                                <th>الحد الأقصى للطلاب</th>
                            @elseif($tab == 'classes')
                                <th>المرحلة التابعة لها</th>
                            @elseif($tab == 'grades')
                                <th>الترتيب</th>
                            @endif
                            
                            <th>تاريخ الحذف</th>
                            <th class="text-end pe-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-soft-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                        <i class="fas fa-{{ $tabs[$tab]['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $item->full_name ?? $item->name }}</h6>
                                        <small class="text-muted">ID: {{ $item->id }}</small>
                                    </div>
                                </div>
                            </td>

                            @if($tab == 'students')
                                <td><span class="badge bg-light text-dark font-monospace">{{ $item->student_number ?? '—' }}</span></td>
                                <td>{{ $item->schoolClass?->name ?? '—' }} / <span class="text-primary">{{ $item->section?->name ?? '—' }}</span></td>
                            @elseif($tab == 'teachers')
                                <td><span class="font-monospace text-muted">{{ $item->national_id ?? '—' }}</span></td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $item->specialization ?? '—' }}</span></td>
                            @elseif($tab == 'parents')
                                <td><span class="font-monospace text-muted">{{ $item->national_id ?? '—' }}</span></td>
                                <td><span class="text-muted" dir="ltr">{{ $item->phone ?? '—' }}</span></td>
                            @elseif($tab == 'subjects')
                                <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $item->code ?? '—' }}</span></td>
                            @elseif($tab == 'sections')
                                <td>
                                    {{ $item->schoolClass?->grade?->name ?? '' }} - 
                                    <span class="fw-semibold">{{ $item->schoolClass?->name ?? '—' }}</span>
                                </td>
                                <td>{{ $item->max_students }} طالب</td>
                            @elseif($tab == 'classes')
                                <td><span class="badge bg-info-subtle text-info">{{ $item->grade?->name ?? '—' }}</span></td>
                            @elseif($tab == 'grades')
                                <td>{{ $item->level ?? '—' }}</td>
                            @endif
                            
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark">{{ $item->deleted_at ? $item->deleted_at->translatedFormat('j F Y') : '—' }}</span>
                                    <span class="text-muted small">{{ $item->deleted_at ? $item->deleted_at->translatedFormat('h:i A') : '' }}</span>
                                </div>
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form action="{{ route('admin.archive.restore', ['type' => $tab, 'id' => $item->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm btn-hover-scale" title="استرجاع">
                                            <i class="fas fa-undo me-1"></i> استرجاع
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.archive.force-delete', ['type' => $tab, 'id' => $item->id]) }}" method="POST" class="force-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-force-delete" title="حذف نهائي">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
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

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .nav-tabs-custom .nav-link {
        transition: all 0.3s ease;
    }
    .nav-tabs-custom .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
    }
    .btn-hover-scale {
        transition: transform 0.2s ease;
    }
    .btn-hover-scale:hover {
        transform: translateY(-1px);
    }
    .custom-table td {
        padding: 1rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // Handle Single Force Delete
        const deleteBtns = document.querySelectorAll('.btn-force-delete');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: 'لن تتمكن من التراجع عن هذا الإجراء! سيتم حذف العنصر بشكل نهائي من قاعدة البيانات.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، احذف نهائياً',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Handle Empty Trash
        const emptyTrashBtn = document.querySelector('.btn-empty-trash');
        if (emptyTrashBtn) {
            emptyTrashBtn.addEventListener('click', function() {
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'تفريغ السلة بالكامل؟',
                    html: 'سيتم الحذف النهائي لجميع العناصر المحذوفة في هذا القسم.<br><strong class="text-danger">لا يمكن التراجع عن هذه العملية!</strong>',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، أفرغ السلة',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
