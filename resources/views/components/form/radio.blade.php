@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'inline' => false,
    'disabled' => false,
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $currentValue = old($dotName, $value);

    // $normalizedOptions = [];
    // foreach ($options as $key => $option) {
    //     if (is_int($key)) {
    //         $normalizedOptions[$option] = $option;
    //     } else {
    //         $normalizedOptions[$key] = $option;
    //     }
    // }


    $isList = is_array($options) && array_is_list($options);
    $normalizedOptions = [];
    foreach ($options as $key => $option) {
        if ($isList) {
            $normalizedOptions[$option] = $option;
        } else {
            $normalizedOptions[$key] = $option;
        }
    }
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

    <div class="radio-group @if($inline) radio-group-inline @endif">
        @foreach($normalizedOptions as $optValue => $optLabel)
            @php
                $optId = $id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $optValue);
                $isChecked = $currentValue !== null && $currentValue !== '' && (string) $currentValue === (string) $optValue;
            @endphp
            <label class="radio-item">
                <input
                    type="radio"
                    name="{{ $name }}"
                    id="{{ $optId }}"
                    value="{{ $optValue }}"
                    @if($isChecked) checked @endif
                    @if($disabled) disabled @endif
                    @if($required) required @endif
                    class="radio-input @if($hasError) is-invalid @endif"
                    {{ $attributes->except(['id', 'class']) }}
                >
                <span class="radio-text">{{ $optLabel }}</span>
            </label>
        @endforeach
    </div>

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>
