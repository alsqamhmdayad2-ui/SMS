@extends('layouts.app')
@section('title', 'قائمة المعلمين')

@section('content')
<div class="page-header">
    <h2>أعضاء هيئة التدريس</h2>
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-secondary">
        <i class="fas fa-plus"></i> إضافة معلم جديد
    </a>
</div>

<!-- Search / Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teachers.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">البحث</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="ابحث بالاسم أو رقم الهوية أو التخصص..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> بحث</button>
                </div>
                @if(request()->filled('search'))
                    <div class="col-md-auto">
                        <a href="{{ route('admin.teachers.index') }}" class="btn btn-light border">
                            <i class="fas fa-times me-1"></i> مسح
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Teachers Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table data-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المعلم</th>
                        <th>رقم الهوية</th>
                        <th>التخصص</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                         style="width:40px;height:40px;min-width:40px;background:var(--gradient-primary);font-size:1rem;">
                                        {{ mb_substr($teacher->first_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $teacher->full_name }}</div>
                                        <div class="text-muted small">{{ $teacher->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td dir="ltr" class="text-muted">{{ $teacher->national_id ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                    {{ $teacher->specialization ?? 'عام' }}
                                </span>
                            </td>
                            <td dir="ltr">{{ $teacher->phone ?? '—' }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> نشط
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-btns d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.teachers.show', $teacher->id) }}"
                                       class="btn btn-sm btn-outline-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.teachers.destroy', $teacher->id) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا المعلم؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-25 d-block"></i>
                                لا يوجد معلمون مطابقون لبحثك.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($teachers, 'hasPages') && $teachers->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
