@props([
    'name' => 'fileup',
    'id' => null,
    'label' => 'Upload de arquivo',
    'buttonLabel' => 'Escolher arquivo',
    'message' => 'Solte aqui para fazer upload',
    'valueMessage' => 'Arquivo selecionado: %s',
    'clearButton' => true,
    'required' => false,
    'accept' => null
])

@php
    $fieldId = $id ?? $name;
@endphp

<div class="upload-area">
    <div class="col-left">
        <div class="message">{{ $message }}</div>
        <div class="value-message"></div>
    </div>
    <div class="col-right">
        @if($clearButton)
            <button class="clear-button admin-btn admin-btn-secondary" type="button" aria-label="Clear" title="Limpar">
                <x-lucide-x class="lucid-icon" />
            </button>
        @endif
        <button type="button" class="admin-btn admin-btn-secondary">{{ $buttonLabel }}</button>
    </div>

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        data-value-msg="{{ $valueMessage }}"
        {{ $required ? 'required' : '' }}
        {{ $accept ? 'accept="' . $accept . '"' : '' }}
        style="display: none;"
    >
</div>

@push('styles')
<style>
.element {
    margin-bottom: 20px;
}

.element label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.upload-area {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: 12px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    transition: all 0.3s ease;
    background-color: var(--input-bg-color, #efefef);
    gap: 1rem;
}

.upload-area.highlight {
    border-color: #4480d3;
    background-color: #e9f5ff;
}

.upload-area.filled {
    border-color: #4480d3;
    background-color: #e9f2ff;
}

.upload-area .col-left {
    flex-grow: 3;
    flex-shrink: 3;
    text-align: left;
}

.upload-area .col-right {
    text-align: right;
}

.upload-area .col-right button:last-child {
    display: block;
}

.upload-area .col-right .clear-button {
    width: 32px;
    margin-bottom: 0.25rem;
    padding: 8px;
    color: #333;
    background-color: rgba(0, 0, 0, 0);
    border-color: rgba(0, 0, 0, 0);
}

.upload-area .message {
    font-size: 1rem;
    color: #555;
    margin-bottom: 10px;
    color: var(--input-color, #333333);
}

.upload-area .value-message {
    font-size: 0.9rem;
    color: #333;
    font-weight: bold;
    margin-top: 5px;
    min-height: 1.2rem;
    color: var(--input-color, #333333);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.upload-area').forEach(wrapper => {
        const input = wrapper.parentElement.querySelector('.upload-area input[type="file"]');
        const valueMsg = wrapper.querySelector('.value-message');
        const chooseBtn = wrapper.querySelector('.col-right button:not(.clear-button)');
        const clearBtn = wrapper.querySelector('.clear-button');
        const valueMsgTemplate = input.dataset.valueMsg || 'Selected file: %s';

        // Update message when file selected
        input.addEventListener('change', () => {
            const fileName = input.files.length > 0 ? input.files[0].name : '';
            valueMsg.innerText = fileName ? valueMsgTemplate.replace('%s', fileName) : '';
            wrapper.classList.toggle('filled', !!fileName);
        });

        // Trigger file input click
        chooseBtn.addEventListener('click', () => input.click());

        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        // Drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            wrapper.addEventListener(eventName, e => {
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

        wrapper.addEventListener('drop', e => {
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
