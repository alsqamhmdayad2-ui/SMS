@extends('layouts.app')
@section('title', 'سلة المهملات والأرشيف')

@section('content')

<div class="page-header">
    <h2><i class="fas fa-trash-restore me-2 text-danger"></i>سلة المهملات (الأرشيف)</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
            <li class="breadcrumb-item active">الأرشيف</li>
        </ol>
    </nav>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-0">
        <ul class="nav nav-tabs nav-fill border-0" style="background: var(--bg-light); border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <li class="nav-item">
                <a class="nav-link border-0 fw-semibold {{ $tab == 'students' ? 'active bg-white text-primary border-bottom border-primary border-3' : 'text-muted' }}" 
                   href="{{ route('admin.archive.index', ['tab' => 'students']) }}">
                    <i class="fas fa-user-graduate me-2"></i>الطلاب المحذوفين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-0 fw-semibold {{ $tab == 'teachers' ? 'active bg-white text-primary border-bottom border-primary border-3' : 'text-muted' }}" 
                   href="{{ route('admin.archive.index', ['tab' => 'teachers']) }}">
                    <i class="fas fa-chalkboard-teacher me-2"></i>المعلمين المحذوفين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-0 fw-semibold {{ $tab == 'parents' ? 'active bg-white text-primary border-bottom border-primary border-3' : 'text-muted' }}" 
                   href="{{ route('admin.archive.index', ['tab' => 'parents']) }}">
                    <i class="fas fa-users me-2"></i>أولياء الأمور
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-0 fw-semibold {{ $tab == 'subjects' ? 'active bg-white text-primary border-bottom border-primary border-3' : 'text-muted' }}" 
                   href="{{ route('admin.archive.index', ['tab' => 'subjects']) }}">
                    <i class="fas fa-book me-2"></i>المواد الدراسية
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        @if($data->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                <h5>لا يوجد عناصر محذوفة في هذا القسم</h5>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الاسم / العنوان</th>
                            @if($tab == 'students')
                                <th>الصف والشعبة</th>
                            @elseif($tab == 'teachers')
                                <th>التخصص</th>
                            @elseif($tab == 'parents')
                                <th>الهوية</th>
                            @elseif($tab == 'subjects')
                                <th>الكود</th>
                            @endif
                            <th>تاريخ الحذف</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->full_name ?? $item->name }}</strong>
                            </td>
                            @if($tab == 'students')
                                <td>{{ $item->schoolClass?->name }} / {{ $item->section?->name }}</td>
                            @elseif($tab == 'teachers')
                                <td>{{ $item->specialization ?? '—' }}</td>
                            @elseif($tab == 'parents')
                                <td>{{ $item->national_id ?? '—' }}</td>
                            @elseif($tab == 'subjects')
                                <td>{{ $item->code ?? '—' }}</td>
                            @endif
                            
                            <td class="text-muted small">
                                {{ $item->deleted_at ? $item->deleted_at->translatedFormat('j F Y h:i A') : '—' }}
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form action="{{ route('admin.archive.restore', ['type' => $tab, 'id' => $item->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="استرجاع">
                                            <i class="fas fa-undo"></i> استرجاع
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.archive.force-delete', ['type' => $tab, 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف النهائي؟ لا يمكن التراجع عن هذا الإجراء.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف نهائي">
                                            <i class="fas fa-trash-alt"></i> حذف نهائي
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
