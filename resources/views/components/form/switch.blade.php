@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => '1',
    'active' => 'Ativado',
    'inactive' => 'Desativado',
    'disabled' => false,
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $isChecked = (bool) old($dotName, $checked);
@endphp

<div class="form-field">
    @if($label)
        <span class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </span>
    @endif

    <div class="switch-container">
        <label class="switch-control">
            <input type="hidden" name="{{ $name }}" value="0">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value }}"
                @if($isChecked) checked @endif
                @if($disabled) disabled @endif
                @if($required) required @endif
                class="switch-native-input"
                {{ $attributes->except(['id', 'class']) }}
            >
            <span class="switch-track @if($hasError) is-invalid @endif">
                <span class="switch-thumb"></span>
            </span>
            @if($active || $inactive)
                <span class="switch-status-text"
                    data-active="{{ $active }}"
                    data-inactive="{{ $inactive }}">
                </span>
            @endif
        </label>
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
    <link rel="stylesheet" href="{{ asset('css/admin/components/switch.css') }}">
@endpush
@endonce
