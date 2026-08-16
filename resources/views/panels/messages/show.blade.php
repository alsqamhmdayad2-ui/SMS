@extends('layouts.app')
@section('title', 'عرض الرسالة')

@push('styles')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .chat-bubble {
        max-width: 80%;
        padding: 1.25rem;
        border-radius: 1rem;
        position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .chat-left {
        align-self: flex-start;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-bottom-right-radius: 0;
    }
    .chat-right {
        align-self: flex-end;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-bottom-left-radius: 0;
    }
    .chat-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    .avatar-left { background-color: #64748b; }
    .avatar-right { background-color: #3b82f6; }
</style>
@endpush

@section('content')
<x-page-header title="{{ $message->subject }}">
    <x-slot:actions>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع للوارد
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-md-10">
        
        <!-- Header Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $message->subject }}</h5>
                    <div class="text-muted small">
                        @if($message->sender_id === auth()->id())
                            {{-- Sender sees all recipients --}}
                            <i class="fas fa-users me-1"></i> إلى: 
                            @foreach($message->recipients as $idx => $rec)
                                {{ $rec->receiver->name ?? 'غير معروف' }}{{ !$loop->last ? '، ' : '' }}
                            @endforeach
                        @else
                            {{-- Recipients only see sender name --}}
                            <i class="fas fa-user me-1"></i> من: {{ $message->sender->name ?? 'غير معروف' }}
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                        <i class="fas fa-clock me-1"></i> {{ $message->created_at->format('Y/m/d h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Thread -->
        <div class="chat-container mb-4">
            
            <!-- Original Message -->
            @php $isMe = $message->sender_id === auth()->id(); @endphp
            <div class="d-flex gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                <div class="chat-avatar {{ $isMe ? 'avatar-right' : 'avatar-left' }} mt-auto shadow-sm">
                    {{ mb_substr($message->sender->name, 0, 1) }}
                </div>
                <div class="chat-bubble {{ $isMe ? 'chat-right' : 'chat-left' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary-subtle">
                        <strong>{{ $message->sender->name }}</strong>
                        <small class="text-muted ms-4">{{ $message->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="chat-body" style="white-space: pre-line;">
                        {{ $message->body }}
                    </div>
                </div>
            </div>

            <!-- Replies -->
            @foreach($message->replies as $reply)
                @php $isMeReply = $reply->sender_id === auth()->id(); @endphp
                <div class="d-flex gap-3 {{ $isMeReply ? 'flex-row-reverse' : '' }}">
                    <div class="chat-avatar {{ $isMeReply ? 'avatar-right' : 'avatar-left' }} mt-auto shadow-sm">
                        {{ mb_substr($reply->sender->name, 0, 1) }}
                    </div>
                    <div class="chat-bubble {{ $isMeReply ? 'chat-right' : 'chat-left' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary-subtle">
                            <strong>{{ $reply->sender->name }}</strong>
                            <small class="text-muted ms-4">{{ $reply->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="chat-body" style="white-space: pre-line;">
                            {{ $reply->body }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        <!-- Reply Form -->
        <div class="card border-0 shadow-sm mt-5">
            <div class="card-body p-4">
                <form action="{{ route('messages.reply', $message->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">كتابة رد...</label>
                        <textarea name="body" rows="4" class="form-control bg-light" required placeholder="اكتب ردك هنا..."></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="fas fa-reply me-1"></i> إرسال الرد
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
