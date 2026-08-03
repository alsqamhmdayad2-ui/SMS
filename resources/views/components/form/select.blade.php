@props([
    'name',
    'label' => null,
    'options' => [], // array of value => label or Objects
    'value' => null, // selected value
    'error' => null,
    'required' => false,
    'placeholder' => '-- اختر --',
    'disabled' => false,
    'class' => ''
])

<div class="mb-3 sms-form-group">
    @if($label)
        <label for="{{ $attributes->get('id', $name) }}" class="form-label fw-semibold text-sms-main">
            {{ $label }}
            @if($required)
                <span class="text-sms-danger">*</span>
            @endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $attributes->get('id', $name) }}" 
        {{ $attributes->merge(['class' => 'form-select ' . ($error ? 'is-invalid ' : '') . $class])->except('id') }}
        @if($required) required @endif
        @if($disabled) disabled @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        {{ $slot }}
        
        @if(!empty($options))
            @foreach($options as $val => $text)
                @php 
                    $isSelected = (string)old($name, $value) === (string)$val; 
                @endphp
                <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif
    </select>
    
    @if($error)
        <div class="invalid-feedback d-block text-sm">
            {{ $error }}
        </div>
    @endif
</div>
