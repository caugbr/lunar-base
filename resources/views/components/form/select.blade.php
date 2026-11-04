@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'multiple' => false,
    'allowInsert' => false,
    'insertLabel' => 'Inserir novo...',
    'insertPlaceholder' => 'Novo valor (Enter ou sair do campo para confirmar)',
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName) || $errors->has($dotName . '.*');

    $inputName = $multiple && !str_ends_with($name, '[]') ? $name . '[]' : $name;

    $isList = is_array($options) && array_is_list($options);
    $normalizedOptions = [];
    foreach ($options as $key => $option) {
        if ($isList) {
            $normalizedOptions[$option] = $option;
        } else {
            $normalizedOptions[$key] = $option;
        }
    }

    $hasInsert = !$multiple && $allowInsert && !empty($insertLabel);

    if ($multiple) {
        $rawValues = old($dotName, $value ?? []);
        if ($rawValues instanceof \Illuminate\Support\Collection) {
            $rawValues = $rawValues->all();
        } elseif (!is_array($rawValues)) {
            $rawValues = ($rawValues !== null && $rawValues !== '') ? [$rawValues] : [];
        }
        $selectedValues = array_map('strval', $rawValues);
    } else {
        $currentValue = old($dotName, $value);
        if ($hasInsert && $currentValue !== null && $currentValue !== '' && !array_key_exists($currentValue, $normalizedOptions)) {
            $normalizedOptions[$currentValue] = $currentValue;
        }
    }
@endphp

<div class="form-field @if($hasInsert) creatable-select-group @endif" @if($hasInsert) data-creatable-select @endif>
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </label>
    @endif

    <div class="select-field-wrapper">
        <select
            name="{{ $inputName }}"
            id="{{ $id }}"
            @if($multiple) multiple @endif
            @if($required) required @endif
            data-select-native
            {{ $attributes->class(['form-control', 'is-invalid' => $hasError]) }}
        >
            @if(!$multiple && $placeholder !== false)
                <option value="">{{ $placeholder ?? '-- Selecione --' }}</option>
            @endif

            @foreach($normalizedOptions as $optValue => $optLabel)
                @php
                    $isSelected = $multiple
                        ? in_array((string) $optValue, $selectedValues, true)
                        : ($currentValue !== null && $currentValue !== '' && (string) $currentValue === (string) $optValue);
                @endphp
                <option value="{{ $optValue }}" @if($isSelected) selected @endif>
                    {{ $optLabel }}
                </option>
            @endforeach

            @if($hasInsert)
                <option value="__insert__">{{ $insertLabel }}</option>
            @endif
        </select>

        @if($hasInsert)
            <input
                type="text"
                id="{{ $id }}_input"
                placeholder="{{ $insertPlaceholder }}"
                autocomplete="off"
                data-select-input
                class="form-control creatable-input"
            >
        @endif
    </div>

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @else
        @error($dotName . '.*')
            <small class="form-error">{{ $message }}</small>
        @enderror
    @enderror
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/form/select.css') }}">
@endpush
@endonce

@if($hasInsert)
    @once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-creatable-select]').forEach(group => {
            const select = group.querySelector('[data-select-native]');
            const input = group.querySelector('[data-select-input]');
            if (!select || !input) return;

            const commitValue = () => {
                if (!group.classList.contains('is-inserting')) return;

                const val = input.value.trim();
                group.classList.remove('is-inserting');

                if (val.length > 0) {
                    let exists = false;
                    for (const opt of select.options) {
                        if (opt.value === val) {
                            exists = true;
                            select.value = opt.value;
                            break;
                        }
                    }

                    if (!exists) {
                        const newOption = document.createElement('option');
                        newOption.value = val;
                        newOption.textContent = val;
                        newOption.selected = true;

                        const insertOption = select.querySelector('option[value="__insert__"]');
                        if (insertOption) {
                            select.insertBefore(newOption, insertOption);
                        } else {
                            select.appendChild(newOption);
                        }
                    }
                } else {
                    select.selectedIndex = 0;
                }

                input.value = '';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const cancelInsertion = () => {
                if (!group.classList.contains('is-inserting')) return;
                group.classList.remove('is-inserting');
                input.value = '';
                select.selectedIndex = 0;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            };

            select.addEventListener('change', () => {
                if (select.value === '__insert__') {
                    group.classList.add('is-inserting');
                    setTimeout(() => input.focus(), 50);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    commitValue();
                    select.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelInsertion();
                    select.focus();
                }
            });

            input.addEventListener('blur', () => {
                commitValue();
            });

            const parentForm = group.closest('form');
            if (parentForm) {
                parentForm.addEventListener('submit', () => {
                    commitValue();
                });
            }
        });
    });
    </script>
    @endpush
    @endonce
@endif
