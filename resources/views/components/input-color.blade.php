@props([
    'name',
    'id' => null,
    'value' => '#3b82f6',
    'label' => null,
])

@php
    $fieldId = $id ?? $name;
    // Se vier do old() da validação do Laravel, usa ele, senão usa o $value ou padrão
    $currentValue = old($name, $value ?: '#3b82f6');
@endphp

<div class="color-picker-group">
    @if($label)
        <label for="{{ $fieldId }}" class="color-picker-label">{{ $label }}</label>
    @endif

    <div class="color-picker-wrapper">
        <input
            type="color"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            oninput="this.nextElementSibling.value = this.value.toUpperCase()"
            {{ $attributes->merge(['class' => 'color-picker-input']) }}
        >
        <output for="{{ $fieldId }}" class="color-picker-output">{{ strtoupper($currentValue) }}</output>
    </div>
</div>

@once
@push('styles')
<style>
    .color-picker-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .color-picker-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .color-picker-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
    }

    .color-picker-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 5rem !important;
        height: 2.25rem;
        padding: 0 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem;
        cursor: pointer;
        background: transparent;
        overflow: hidden;
        transition: border-color 0.15s ease-in-out;
    }

    .color-picker-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    /* Remove bordas nativas e padding feio do navegador */
    .color-picker-input::-webkit-color-swatch-wrapper {
        padding: 0;
    }
    .color-picker-input::-webkit-color-swatch {
        border: none;
        border-radius: 0.25rem;
    }
    .color-picker-input::-moz-color-swatch {
        border: none;
        border-radius: 0.25rem;
    }

    /* O valor Hexadecimal ao lado */
    .color-picker-output {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        background-color: #f3f4f6;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        min-width: 5.5rem;
        text-align: center;
        user-select: all; /* Facilita copiar o código clicando nele */
    }
</style>
@endpush
@endonce
