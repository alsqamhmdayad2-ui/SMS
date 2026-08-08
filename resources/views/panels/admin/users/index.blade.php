@extends('layouts.app')
@section('title', 'إدارة المستخدمين والصلاحيات')

@section('content')

<x-page-header title="إدارة المستخدمين والصلاحيات">
    <x-slot:actions>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> إضافة مستخدم
        </a>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'المستخدمين']
]" />


{{-- Stats --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-md-3">
        <x-dashboard.stat-card title="إجمالي المستخدمين" value="{{ $users->total() }}" icon="fas fa-users" color="primary" />
    </div>
    @foreach($roles as $role)
    <div class="col-12 col-md-3">
        <x-dashboard.stat-card
            title="{{ match($role->name) { 'admin' => 'مدير النظام', 'teacher' => 'معلم', 'student' => 'طالب', 'parent' => 'ولي أمر', default => $role->name } }}"
            value="{{ \App\Models\User::role($role->name)->count() }}"
            icon="{{ match($role->name) { 'admin' => 'fas fa-user-shield', 'teacher' => 'fas fa-chalkboard-teacher', 'student' => 'fas fa-user-graduate', 'parent' => 'fas fa-user-friends', default => 'fas fa-user' } }}"
            color="{{ match($role->name) { 'admin' => 'danger', 'teacher' => 'info', 'student' => 'success', 'parent' => 'warning', default => 'secondary' } }}"
        />
    </div>
    @endforeach
</div>

{{-- Filters + Search --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">بحث</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو رقم الهوية..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">الدور</label>
                <select name="role" class="form-select">
                    <option value="">-- جميع الأدوار --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $roleFilter == $role->name ? 'selected' : '' }}>
                            {{ match($role->name) { 'admin' => 'مدير النظام', 'teacher' => 'معلم', 'student' => 'طالب', 'parent' => 'ولي أمر', default => $role->name } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>المستخدم</th>
                        <th>رقم الهوية</th>
                        <th>الدور</th>
                        <th>تاريخ الإنشاء</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="ps-3 text-muted">{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar-sm d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                     style="width:38px;height:38px;font-size:14px;background:var(--gradient-primary);flex-shrink:0;">
                                    {{ mb_substr($user->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-success-subtle text-success" style="font-size:10px;">أنت</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted fw-semibold" style="letter-spacing: 0.5px;">{{ $user->national_id ?? '-' }}</td>
                        <td>
                            @php $role = $user->roles->first() @endphp
                            @if($role)
                                <span class="badge rounded-pill
                                    {{ match($role->name) {
                                        'admin' => 'bg-danger',
                                        'teacher' => 'bg-info',
                                        'student' => 'bg-success',
                                        'parent' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    } }}">
                                    {{ match($role->name) {
                                        'admin' => 'مدير',
                                        'teacher' => 'معلم',
                                        'student' => 'طالب',
                                        'parent' => 'ولي أمر',
                                        default => $role->name
                                    } }}
                                </span>
                            @else
                                <span class="text-muted small">بدون دور</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-primary" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-2x mb-3 d-block opacity-25"></i>
                            لا توجد مستخدمين مطابقون للبحث.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection
