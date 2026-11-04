@props([
    'name',
    'label' => null,
    'value' => '#3b82f6',
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $currentValue = old($dotName, $value ?: '#3b82f6');
@endphp

<div class="form-field">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </label>
    @endif

    <div class="color-picker-wrapper">
        <input
            type="color"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            @if($required) required @endif
            oninput="this.nextElementSibling.value = this.value.toUpperCase()"
            {{ $attributes->class(['color-picker-input', 'is-invalid' => $hasError]) }}
        >
        <output for="{{ $id }}" class="color-picker-output">{{ strtoupper($currentValue) }}</output>
    </div>

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/form/color.css') }}">
@endpush
@endonce
