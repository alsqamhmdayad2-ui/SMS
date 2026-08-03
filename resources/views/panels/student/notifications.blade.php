@extends('layouts.app')
@section('title', 'الإشعارات - الطالب')

@section('content')

<x-page-header title="الإشعارات">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الإشعارات']
]" />

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-light pt-4 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="fas fa-bell ms-2 text-warning"></i>جميع الإشعارات</h5>
        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-check-double ms-1"></i> تحديد الكل كمقروء</button>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $typeClass = 'primary';
                    $icon = 'bell';
                    
                    if(str_contains($notification->type ?? '', 'Exam')) {
                        $typeClass = 'info'; $icon = 'file-alt';
                    } elseif(str_contains($notification->type ?? '', 'Result')) {
                        $typeClass = 'success'; $icon = 'check-circle';
                    } elseif(str_contains($notification->type ?? '', 'Attendance')) {
                        $typeClass = 'warning'; $icon = 'exclamation-triangle';
                    }
                @endphp
                <div class="list-group-item p-4 {{ $isUnread ? 'border-start border-4 border-'.$typeClass.' bg-'.$typeClass.' bg-opacity-10' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="fas fa-{{ $icon }} ms-2 text-{{ $typeClass }}"></i>{{ $notification->data['title'] ?? 'إشعار' }}</h6>
                            <p class="mb-1 text-muted small">{{ $notification->data['message'] ?? 'لا يوجد تفاصيل إضافية' }}</p>
                            <small class="text-muted"><i class="far fa-clock ms-1"></i> {{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        @if($isUnread)
                            <span class="badge bg-{{ $typeClass }} rounded-pill">جديد</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="list-group-item p-5 text-center text-muted">
                    <i class="fas fa-bell-slash fs-1 mb-3 opacity-50 d-block"></i>
                    لا توجد إشعارات حالياً
                </div>
            @endforelse
        </div>
        <div class="p-3">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
