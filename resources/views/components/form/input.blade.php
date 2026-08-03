@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'error' => null,
    'required' => false,
    'placeholder' => null,
    'disabled' => false,
    'readonly' => false,
    'class' => ''
])

<div class="mb-3 sms-form-group">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-semibold text-sms-main">
            {{ $label }}
            @if($required)
                <span class="text-sms-danger">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-control ' . ($error ? 'is-invalid ' : '') . $class]) }}
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
    >
    
    @if($error)
        <div class="invalid-feedback d-block text-sm">
            {{ $error }}
        </div>
    @endif
</div>
