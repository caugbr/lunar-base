@props([
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'rows' => 5,
    'hint' => null,
])

@php
    $fieldId = $id ?? $name;
    $currentValue = old($name, $value);
@endphp

<div class="mini-editor-group">
    @if($label)
        <label for="{{ $fieldId }}" class="mini-editor-label">{{ $label }}</label>
    @endif

    <div class="mini-editor-wrapper">
        {{-- Barra de Ferramentas --}}
        <div class="mini-editor-toolbar" data-target="{{ $fieldId }}">
            <button type="button" onclick="formatTag('{{ $fieldId }}', 'b')" title="Negrito"><b>B</b></button>
            <button type="button" onclick="formatTag('{{ $fieldId }}', 'i')" title="Itálico"><i>I</i></button>
            <button type="button" onclick="formatTag('{{ $fieldId }}', 'u')" title="Sublinhado"><u>U</u></button>
            <button type="button" onclick="formatTag('{{ $fieldId }}', 's')" title="Tachado"><s>S</s></button>
            <button type="button" onclick="formatLink('{{ $fieldId }}')" title="Link">🔗</button>

            <span class="toolbar-separator"></span>

            {{-- Botão Limpar Formatação (Tx) --}}
            <button type="button" onclick="clearFormatting('{{ $fieldId }}')" title="Limpar formatação (remover tags HTML)">
                {{-- <span style="text-decoration: line-through; font-weight: bold; font-size: 0.8rem;">T</span><small style="font-size: 0.65rem; margin-left: 1px;">x</small> --}}
                &#x1F9FD;
            </button>
        </div>

        {{-- Textarea Puro --}}
        <textarea
            id="{{ $fieldId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            {{ $attributes->merge(['class' => 'mini-editor-textarea']) }}
        >{!! $currentValue !!}</textarea>
    </div>

    @if($hint)
        <small class="mini-editor-hint">{{ $hint }}</small>
    @endif
</div>

{{-- Script nativo único --}}
@once
<script>
    function formatTag(textareaId, tag) {
        const textarea = document.getElementById(textareaId);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);

        const openTag = `<${tag}>`;
        const closeTag = `</${tag}>`;

        // Se nada selecionado, insere as tags e deixa o cursor no meio
        if (start === end) {
            textarea.setRangeText(openTag + closeTag, start, end, 'end');
            textarea.selectionStart = textarea.selectionEnd = start + openTag.length;
        } else {
            // Envolve o texto selecionado
            textarea.setRangeText(openTag + selectedText + closeTag, start, end, 'select');
        }

        textarea.focus();
    }

    async function formatLink(textareaId) {
        const textarea = document.getElementById(textareaId);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);

        const url = await Dialog.prompt('Digite a URL do link (ex: https://...):', 'https://', 'Link');
        if (!url || url === 'https://') return;

        const linkText = selectedText || prompt('Texto do link:', 'clique aqui') || url;
        const html = `<a href="${url}" target="_blank">${linkText}</a>`;

        textarea.setRangeText(html, start, end, 'select');
        textarea.focus();
    }

    function clearFormatting(textareaId) {
        const textarea = document.getElementById(textareaId);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        // Regex que remove qualquer tag HTML (<tag>, </tag>, <tag />)
        const stripHtml = (html) => html.replace(/<\/?[^>]+(>|$)/g, '');

        // Se há texto selecionado, limpa só a seleção
        if (start !== end) {
            const selectedText = textarea.value.substring(start, end);
            const cleanText = stripHtml(selectedText);
            textarea.setRangeText(cleanText, start, end, 'select');
        } else {
            // Se nada selecionado, limpa o texto inteiro
            textarea.value = stripHtml(textarea.value);
        }

        textarea.focus();
    }
</script>

<style>
    .mini-editor-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .toolbar-separator {
        width: 1px;
        height: 1.15rem;
        background-color: #d1d5db;
        margin: 0 0.25rem;
    }
    .mini-editor-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
    }
    .mini-editor-wrapper {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        background: #fff;
        overflow: hidden;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .mini-editor-wrapper:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
    .mini-editor-toolbar {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.35rem 0.5rem;
    }
    .mini-editor-toolbar button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 0.25rem;
        cursor: pointer;
        color: #374151;
        font-size: 0.875rem;
        transition: background-color 0.1s, border-color 0.1s;
    }
    .mini-editor-toolbar button:hover {
        background-color: #e5e7eb;
        border-color: #d1d5db;
    }
    .mini-editor-textarea {
        width: 100%;
        border: none !important;
        padding: 0.75rem;
        outline: none;
        resize: vertical;
        font-family: inherit;
        font-size: 0.95rem;
        line-height: 1.5;
        box-sizing: border-box;
    }
    .mini-editor-hint {
        color: #6b7280;
        font-size: 0.8rem;
    }
</style>
@endonce
