@props([
    'name',
    'label' => null,
    'value' => null,
    'unique' => false,
    'inline' => false,
    'disabled' => false,
    'required' => false,
    'help' => null,
])

@php
    $types = \App\Support\PublicationTypes::labels();

    $resolvedValue = $value;
    if (!$unique) {
        if (is_array($value)) {
            $resolvedValue = $value;
        } elseif (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            $resolvedValue = is_array($decoded)
                ? $decoded
                : array_map('trim', explode(',', $value));
        } else {
            $resolvedValue = [];
        }
    }
@endphp

@if($unique)
    <x-form.radio
        :name="$name"
        :label="$label"
        :options="$types"
        :value="$resolvedValue"
        :inline="$inline"
        :disabled="$disabled"
        :required="$required"
        :help="$help"
        {{ $attributes }}
    />
@else
    <x-form.checkbox
        :name="$name"
        :label="$label"
        :options="$types"
        :value="$resolvedValue"
        :inline="$inline"
        :disabled="$disabled"
        :required="$required"
        :help="$help"
        {{ $attributes }}
    />
@endif
