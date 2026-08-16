@extends('layouts.app')
@section('title', 'الرسائل المرسلة')

@push('styles')
<style>
    .message-list .message-item {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
        cursor: pointer;
    }
    .message-list .message-item:hover {
        background-color: #f8fafc;
    }
    .message-avatar {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #0f172a;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .message-subject {
        color: #1e293b;
        text-decoration: none;
    }
    .message-preview {
        color: #64748b;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
        display: inline-block;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<x-page-header title="الرسائل - المرسلة">
    <x-slot:actions>
        <a href="{{ route('messages.create') }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i> رسالة جديدة
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="{{ route('messages.index') }}" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center py-3">
                <span><i class="fas fa-inbox me-2 text-muted"></i> صندوق الوارد</span>
                @php
                    $unreadCount = \App\Models\MessageRecipient::where('receiver_id', auth()->id())->whereNull('read_at')->count();
                @endphp
                @if($unreadCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('messages.sent') }}" class="list-group-item list-group-item-action active border-0 py-3">
                <i class="fas fa-paper-plane me-2"></i> الرسائل المرسلة
            </a>
        </div>
    </div>

    <!-- Sent List -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane text-secondary me-2"></i> الرسائل المرسلة</h5>
            </div>
            <div class="card-body p-0">
                @if($messages->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-paper-plane fa-3x mb-3 opacity-25"></i>
                        <h5>لا توجد رسائل مرسلة</h5>
                    </div>
                @else
                    <div class="message-list">
                        @foreach($messages as $msg)
                            <div class="message-item p-3" onclick="window.location='{{ route('messages.show', $msg->id) }}'">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="message-avatar">
                                        <i class="fas fa-users fa-sm"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <div class="text-dark">
                                                إلى: {{ $msg->recipients->count() }} مستلم(ين)
                                            </div>
                                            <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div>
                                            <span class="message-subject">{{ $msg->subject }}</span>
                                            <span class="text-muted mx-1">-</span>
                                            <span class="message-preview">{{ strip_tags($msg->body) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @if($messages->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $messages->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
