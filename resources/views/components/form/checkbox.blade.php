@props([
    'name',
    'label' => null,
    'text' => null,
    'options' => [],
    'value' => '1',
    'checked' => false,
    'inline' => false,
    'disabled' => false,
    'required' => false,
    'help' => null,
])

@php
    $isGroup = !empty($options);
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName) || $errors->has($dotName . '.*');

    if ($isGroup) {
        $normalizedOptions = [];
        foreach ($options as $key => $option) {
            if (is_int($key)) {
                $normalizedOptions[$option] = $option;
            } else {
                $normalizedOptions[$key] = $option;
            }
        }
        $selectedValues = (array) old($dotName, $value ?? []);
        $inputName = str_ends_with($name, '[]') ? $name : $name . '[]';
    } else {
        $isChecked = old($dotName) !== null ? (bool) old($dotName) : (bool) $checked;
        $boxText = $text ?? $label;
        $topLabel = $text ? $label : null;
    }
@endphp

<div class="form-field">
    @if($isGroup)
        @if($label)
            <span class="form-label">
                {{ $label }}
                @if($required)
                    <span class="form-required">*</span>
                @endif
            </span>
        @endif

        <div class="checkbox-group @if($inline) checkbox-group-inline @endif">
            @foreach($normalizedOptions as $optValue => $optLabel)
                @php
                    $optId = $id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $optValue);
                    $isOptChecked = in_array((string) $optValue, array_map('strval', $selectedValues), true);
                @endphp
                <label class="checkbox-item">
                    <input
                        type="checkbox"
                        name="{{ $inputName }}"
                        id="{{ $optId }}"
                        value="{{ $optValue }}"
                        @if($isOptChecked) checked @endif
                        @if($disabled) disabled @endif
                        class="checkbox-input @if($hasError) is-invalid @endif"
                        {{ $attributes->except(['id', 'class']) }}
                    >
                    <span class="checkbox-text">{{ $optLabel }}</span>
                </label>
            @endforeach
        </div>
    @else
        @if($topLabel)
            <span class="form-label">
                {{ $topLabel }}
                @if($required)
                    <span class="form-required">*</span>
                @endif
            </span>
        @endif

        <div class="checkbox-single">
            <input type="hidden" name="{{ $name }}" value="0">
            <label class="checkbox-item">
                <input
                    type="checkbox"
                    name="{{ $name }}"
                    id="{{ $id }}"
                    value="{{ $value }}"
                    @if($isChecked) checked @endif
                    @if($disabled) disabled @endif
                    @if($required) required @endif
                    class="checkbox-input @if($hasError) is-invalid @endif"
                    {{ $attributes->except(['id', 'class']) }}
                >
                @if($boxText)
                    <span class="checkbox-text">
                        {{ $boxText }}
                        @if($required && !$topLabel)
                            <span class="form-required">*</span>
                        @endif
                    </span>
                @endif
            </label>
        </div>
    @endif

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/form/checkbox.css') }}">
@endpush
@endonce
