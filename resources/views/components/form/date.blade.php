@props([
    'name',
    'label' => null,
    'type' => 'date',
    'value' => null,
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);

    // Formata automaticamente objetos Carbon ou DateTime
    $resolvedValue = $value;
    if ($resolvedValue instanceof \DateTimeInterface) {
        $resolvedValue = match ($type) {
            'date' => $resolvedValue->format('Y-m-d'),
            'time' => $resolvedValue->format('H:i'),
            'datetime-local' => $resolvedValue->format('Y-m-d\TH:i'),
            'month' => $resolvedValue->format('Y-m'),
            default => $resolvedValue->format('Y-m-d'),
        };
    }

    $currentValue = old($dotName, $resolvedValue);
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

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $currentValue }}"
        @if($required) required @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $hasError]) }}
    >

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>
