@props([
    'name',
    'label' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'placeholder' => null,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
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
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}" 
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'form-control ' . ($error ? 'is-invalid ' : '') . $class]) }}
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
    >{{ old($name, $value) }}</textarea>
    
    @if($error)
        <div class="invalid-feedback d-block text-sm">
            {{ $error }}
        </div>
    @endif
</div>
