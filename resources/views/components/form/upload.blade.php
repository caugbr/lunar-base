@props([
    'name',
    'label' => null,
    'buttonLabel' => 'Escolher arquivo',
    'message' => 'Solte aqui para fazer upload',
    'valueMessage' => 'Arquivo selecionado: %s',
    'clearButton' => true,
    'required' => false,
    'accept' => null,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
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

    <div class="upload-area @if($hasError) is-invalid @endif" data-upload-area>
        <div class="upload-col-left">
            <div class="upload-message">{{ $message }}</div>
            <div class="upload-value-message" data-value-message></div>
        </div>

        <div class="upload-col-right">
            @if($clearButton)
                <button class="upload-clear-btn" type="button" aria-label="Limpar arquivo" title="Limpar" data-clear-btn>
                    <x-lucide-x class="lucid-icon" />
                </button>
            @endif
            <button type="button" class="admin-btn admin-btn-secondary" data-choose-btn>{{ $buttonLabel }}</button>
        </div>

        <input
            type="file"
            name="{{ $name }}"
            id="{{ $id }}"
            data-file-input
            data-value-msg="{{ $valueMessage }}"
            @if($required) required @endif
            @if($accept) accept="{{ $accept }}" @endif
            class="upload-native-input"
            {{ $attributes->except(['id', 'class']) }}
        >
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
<link rel="stylesheet" href="{{ asset('css/admin/components/form/upload.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-upload-area]').forEach(wrapper => {
        const input = wrapper.querySelector('[data-file-input]');
        const valueMsg = wrapper.querySelector('[data-value-message]');
        const chooseBtn = wrapper.querySelector('[data-choose-btn]');
        const clearBtn = wrapper.querySelector('[data-clear-btn]');
        const valueMsgTemplate = input.dataset.valueMsg || 'Arquivo selecionado: %s';

        if (!input) return;

        input.addEventListener('change', () => {
            const fileName = input.files.length > 0 ? input.files[0].name : '';
            valueMsg.textContent = fileName ? valueMsgTemplate.replace('%s', fileName) : '';
            wrapper.classList.toggle('filled', Boolean(fileName));
        });

        if (chooseBtn) {
            chooseBtn.addEventListener('click', () => input.click());
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            wrapper.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            wrapper.addEventListener(eventName, () => wrapper.classList.add('highlight'));
        });

        ['dragleave', 'drop'].forEach(eventName => {
            wrapper.addEventListener(eventName, () => wrapper.classList.remove('highlight'));
        });

        wrapper.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
});
</script>
@endpush
@endonce
