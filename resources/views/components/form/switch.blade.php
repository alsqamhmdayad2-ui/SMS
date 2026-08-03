@props([
    'name',
    'label',
    'value' => 1,
    'checked' => false,
    'disabled' => false,
    'error' => null,
    'class' => ''
])

<div class="form-check form-switch sms-form-switch mb-2">
    <input 
        class="form-check-input {{ $error ? 'is-invalid' : '' }} {{ $class }}" 
        type="checkbox" 
        role="switch"
        name="{{ $name }}" 
        id="{{ $name }}_{{ $value }}" 
        value="{{ $value }}"
        {{ (old($name) == $value || $checked) ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    <label class="form-check-label" for="{{ $name }}_{{ $value }}">
        {{ $label }}
    </label>
    @if($error)
        <div class="invalid-feedback d-block text-sm">
            {{ $error }}
        </div>
    @endif
</div>
