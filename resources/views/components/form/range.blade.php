@props([
    'name',
    'label' => null,
    'value' => 0,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'unit' => '',
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $currentValue = old($dotName, $value ?? $min);
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

    <div class="range-field-wrapper">
        <input
            type="range"
            name="{{ $name }}"
            id="{{ $id }}"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $currentValue }}"
            data-range-input
            data-unit="{{ $unit }}"
            @if($required) required @endif
            {{ $attributes->class(['range-input', 'is-invalid' => $hasError]) }}
        >
        <output for="{{ $id }}" class="range-output" data-range-output>{{ $currentValue }}{{ $unit }}</output>
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
<link rel="stylesheet" href="{{ asset('css/admin/components/form/range.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-range-input]').forEach(input => {
        const output = input.parentElement.querySelector('[data-range-output]');
        const unit = input.dataset.unit || '';
        if (!output) return;

        input.addEventListener('input', () => {
            output.textContent = input.value + unit;
        });
    });
});
</script>
@endpush
@endonce
