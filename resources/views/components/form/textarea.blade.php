@props([
    'name',
    'label' => null,
    'value' => '',
    'rows' => 5,
    'required' => false,
    'help' => null,
    'editable' => false,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $currentValue = old($dotName, $value);
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

    @if($editable)
        <div class="mini-editor-wrapper @if($hasError) is-invalid @endif" data-mini-editor>
            <div class="mini-editor-toolbar">
                <button type="button" data-editor-command="tag" data-tag="b" title="Negrito">
                    <x-lucide-bold class="lucid-icon" />
                </button>
                <button type="button" data-editor-command="tag" data-tag="i" title="Itálico">
                    <x-lucide-italic class="lucid-icon" />
                </button>
                <button type="button" data-editor-command="tag" data-tag="u" title="Sublinhado">
                    <x-lucide-underline class="lucid-icon" />
                </button>
                <button type="button" data-editor-command="tag" data-tag="s" title="Tachado">
                    <x-lucide-strikethrough class="lucid-icon" />
                </button>
                <button type="button" data-editor-command="link" title="Link">
                    <x-lucide-link-2 class="lucid-icon" />
                </button>

                <span class="toolbar-separator"></span>

                <button type="button" data-editor-command="clear" title="Limpar formatação">
                    <x-lucide-eraser class="lucid-icon" />
                </button>
            </div>

            <textarea
                id="{{ $id }}"
                name="{{ $name }}"
                rows="{{ $rows }}"
                @if($required) required @endif
                {{ $attributes->class(['mini-editor-textarea']) }}
            >{{ $currentValue }}</textarea>
        </div>
    @else
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            @if($required) required @endif
            {{ $attributes->class(['form-control', 'is-invalid' => $hasError]) }}
        >{{ $currentValue }}</textarea>
    @endif

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>

@if($editable)
    @once
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/components/form/textarea-editor.css') }}">
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const applyTag = (textarea, tag) => {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const openTag = `<${tag}>`;
            const closeTag = `</${tag}>`;

            if (start === end) {
                textarea.setRangeText(openTag + closeTag, start, end, 'end');
                textarea.selectionStart = textarea.selectionEnd = start + openTag.length;
            } else {
                textarea.setRangeText(openTag + selectedText + closeTag, start, end, 'select');
            }

            textarea.focus();
        };

        const applyLink = async (textarea) => {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);

            let url = '';
            if (window.Dialog && typeof Dialog.prompt === 'function') {
                url = await Dialog.prompt('Digite a url do link:', 'https://', 'Link');
            } else {
                url = window.prompt('Digite a url do link:', 'https://');
            }

            if (!url || url.trim() === '' || url === 'https://') return;

            const linkText = selectedText || window.prompt('Texto do link:', 'clique aqui') || url;
            const markup = `<a href="${url.trim()}" target="_blank">${linkText}</a>`;

            textarea.setRangeText(markup, start, end, 'select');
            textarea.focus();
        };

        const clearMarkup = (textarea) => {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const strip = (val) => val.replace(/<\/?[^>]+(>|$)/g, '');

            if (start !== end) {
                const selectedText = textarea.value.substring(start, end);
                textarea.setRangeText(strip(selectedText), start, end, 'select');
            } else {
                textarea.value = strip(textarea.value);
            }

            textarea.focus();
        };

        document.addEventListener('click', (e) => {
            const button = e.target.closest('[data-editor-command]');
            if (!button) return;

            const wrapper = button.closest('[data-mini-editor]');
            if (!wrapper) return;

            const textarea = wrapper.querySelector('textarea');
            if (!textarea) return;

            const command = button.dataset.editorCommand;

            if (command === 'tag') {
                applyTag(textarea, button.dataset.tag);
            } else if (command === 'link') {
                applyLink(textarea);
            } else if (command === 'clear') {
                clearMarkup(textarea);
            }
        });
    });
    </script>
    @endpush
    @endonce
@endif
