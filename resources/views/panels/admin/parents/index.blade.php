@extends('layouts.app')
@section('title', 'قائمة أولياء الأمور')

@section('content')
<div class="page-header">
    <h2>أولياء الأمور</h2>
    <a href="{{ route('admin.parents.create') }}" class="btn btn-secondary">
        <i class="fas fa-plus"></i> إضافة ولي أمر جديد
    </a>
</div>

<!-- Search / Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.parents.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">البحث</label>
                    <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو رقم الهوية أو الجوال..." value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> بحث</button>
                </div>
                @if(request()->filled('search'))
                    <div class="col-md-auto">
                        <a href="{{ route('admin.parents.index') }}" class="btn btn-light border"><i class="fas fa-times me-1"></i> مسح</a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table data-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ولي الأمر</th>
                        <th>صلة القرابة</th>
                        <th>رقم الهوية</th>
                        <th>الجوال الأول</th>
                        <th>المهنة</th>
                        <th class="text-center">عدد الطلاب</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                         style="width:40px;height:40px;min-width:40px;background:var(--gradient-primary);font-size:1rem;">
                                        {{ mb_substr($parent->first_name ?? $parent->full_name ?? '؟', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">
                                            @if($parent->first_name)
                                                {{ $parent->first_name }} {{ $parent->father_name }}
                                                {{ $parent->grandfather_name }} {{ $parent->family_name }}
                                            @else
                                                {{ $parent->full_name ?? 'غير محدد' }}
                                            @endif
                                        </div>
                                        <div class="text-muted small">{{ $parent->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $types = ['Father' => 'الأب', 'Mother' => 'الأم', 'Guardian' => 'وصي قانوني'];
                                @endphp
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                    {{ $types[$parent->guardian_type] ?? $parent->guardian_type }}
                                </span>
                            </td>
                            <td dir="ltr">{{ $parent->national_id }}</td>
                            <td dir="ltr">{{ $parent->phone_1 ?? $parent->phone ?? '-' }}</td>
                            <td>{{ $parent->occupation ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                    <i class="fas fa-user-graduate me-1"></i>
                                    {{ $parent->students_count }} طالب
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-btns d-flex justify-content-center gap-2">
                                    @if(Route::has('admin.parents.show'))
                                        <a href="{{ route('admin.parents.show', $parent->id) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف ولي الأمر هذا؟');">
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
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">لا يوجد أولياء أمور مطابقين لبحثك.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parents->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $parents->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
