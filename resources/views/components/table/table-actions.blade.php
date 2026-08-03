@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'deleteId' => null,
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'd-flex gap-2 ' . $class]) }}>
    {{ $slot }}
    
    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="btn btn-sm btn-light text-sms-primary" title="عرض">
            <i class="fas fa-eye"></i>
        </a>
    @endif
    
    @if($editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-light text-sms-warning" title="تعديل">
            <i class="fas fa-edit"></i>
        </a>
    @endif
    
    @if($deleteUrl)
        <button 
            type="button" 
            class="btn btn-sm btn-light text-sms-danger" 
            title="حذف"
            data-url="{{ $deleteUrl }}"
            @if($deleteId) data-id="{{ $deleteId }}" @endif
            data-action="delete"
        >
            <i class="fas fa-trash"></i>
        </button>
    @endif
</div>
