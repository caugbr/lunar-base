@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'allowInsert' => true,
    'insertLabel' => 'Inserir novo...',
    'insertPlaceholder' => 'Novo valor (Enter depois de digitar)',
    'help' => null,
    'required' => false,
    'id' => null,
])

@php
$id = $id ?? $name;
$selectedValue = $value; // old($name, $value);

// Normaliza options para ['value' => 'label']
$normalizedOptions = [];
foreach ($options as $key => $option) {
    if (is_int($key)) {
        // Formato ['value1', 'value2']
        $normalizedOptions[$option] = $option;
    } else {
        // Formato ['value' => 'label']
        $normalizedOptions[$key] = $option;
    }
}

$hasInsert = $allowInsert && !empty($insertLabel);
$wrapperId = $id . '_wrapper';
$inputId = $id . '_input';
@endphp

<div class="form-group" id="{{ $wrapperId }}">
    @if($label)
        <label for="{{ $id }}">{{ $label }}{!! $required ? ' <span class="required">*</span>' : '' !!}</label>
    @endif

    <select name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}>
        @if($placeholder !== false)
            <option value="">{{ $placeholder ?? '-- Selecione --' }}</option>
        @endif

        @foreach($normalizedOptions as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ $selectedValue == $optValue ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach

        @if($hasInsert)
            <option value="__insert__">{{ $insertLabel }}</option>
        @endif
    </select>

    @if($hasInsert)
        <input type="text"
               id="{{ $inputId }}"
               placeholder="{{ $insertPlaceholder }}"
               autocomplete="off">
    @endif

    @if($help)
        <small>{{ $help }}</small>
    @endif

    @error($name)
        <small class="error">{{ $message }}</small>
    @enderror
</div>

@if($hasInsert)
@once
@push('styles')
<style>
    .form-group [id$="_input"] {
        display: none;
    }
    .form-group[data-insert-mode] [id$="_input"] {
        display: block;
    }
    .form-group[data-insert-mode] select {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        const wrappers = document.querySelectorAll('.form-group[id$="_wrapper"]');

        wrappers.forEach(wrapper => {
            const select = wrapper.querySelector('select');
            const input = wrapper.querySelector('input[id$="_input"]');
            if (!select || !input) return;

            const undo = () => {
                if (!wrapper.hasAttribute('data-insert-mode')) return;

                wrapper.removeAttribute('data-insert-mode');

                if (input.value) {
                    // Verifica se já existe
                    let exists = false;
                    for (let opt of select.options) {
                        if (opt.value === input.value) {
                            exists = true;
                            select.value = opt.value;
                            break;
                        }
                    }

                    if (!exists) {
                        const option = document.createElement('option');
                        option.value = input.value;
                        option.textContent = input.value;
                        option.selected = true;
                        select.insertBefore(option, select.lastElementChild);
                    }
                }

                input.value = '';
                select.focus();
            };

            const cancel = () => {
                if (!wrapper.hasAttribute('data-insert-mode')) return;
                wrapper.removeAttribute('data-insert-mode');
                input.value = '';
                select.selectedIndex = 0;
                select.focus();
            };

            select.addEventListener('change', function() {
                if (this.value === '__insert__') {
                    wrapper.setAttribute('data-insert-mode', '');
                    setTimeout(() => input.focus(), 80);
                } else {
                    wrapper.removeAttribute('data-insert-mode');
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    undo();
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    cancel();
                }
            });

            // Clica fora cancela
            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    cancel();
                }
            });
        });
    })();
</script>
@endpush
@endonce
@endif
