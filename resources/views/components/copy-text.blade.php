@props([
    'text' => '',
])

@php
    $content = $text ?: $slot;
@endphp

<div class="copy-text-box">
    <span class="copy-text-content">{{ $content }}</span>
    <button type="button"
            class="copy-text-btn"
            data-copy="{{ $content }}"
            aria-label="Copiar texto">
        <span class="icon-copy">
            <x-lucide-copy class="lucid-icon" />
        </span>
        <span class="icon-check">
            <x-lucide-check class="lucid-icon" />
        </span>
        <span class="copy-tooltip">Copiar</span>
    </button>
</div>

@once
@push('styles')
<style>
/* Container principal */
.copy-text-box {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background-color: var(--color-bg-dark, #f8fafc);
    border: 1px solid var(--color-border, #cbd5e1);
    border-radius: 6px;
    padding: 6px 10px;
    max-width: 100%;
    box-sizing: border-box;
}

/* Área do texto */
.copy-text-box .copy-text-content {
    font-family: monospace;
    font-size: 0.85rem;
    color: var(--color-text, #1e293b);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    user-select: all;
}

/* Botão de ação */
.copy-text-box .copy-text-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    padding: 4px;
    border-radius: 4px;
    cursor: pointer;
    color: var(--color-text-muted, #64748b);
    transition: background-color 0.15s ease, color 0.15s ease;
}

.copy-text-box .copy-text-btn:hover {
    background-color: var(--color-border, #e2e8f0);
    color: var(--color-text, #0f172a);
}

/* Controle dos ícones via CSS puro */
.copy-text-box .copy-text-btn .icon-copy,
.copy-text-box .copy-text-btn .icon-check {
    display: flex;
    align-items: center;
    justify-content: center;
}

.copy-text-box .copy-text-btn .icon-check {
    display: none;
}

.copy-text-box .copy-text-btn.is-copied {
    color: #16a34a;
}

.copy-text-box .copy-text-btn.is-copied .icon-copy {
    display: none;
}

.copy-text-box .copy-text-btn.is-copied .icon-check {
    display: flex;
}

.copy-text-box .copy-text-btn .lucid-icon {
    width: 15px;
    height: 15px;
}

/* Tooltip flutuante */
.copy-text-box .copy-tooltip {
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background-color: #0f172a;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 500;
    padding: 3px 7px;
    border-radius: 4px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.15s ease, visibility 0.15s ease;
    z-index: 10;
}

.copy-text-box .copy-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 4px;
    border-style: solid;
    border-color: #0f172a transparent transparent transparent;
}

.copy-text-box .copy-text-btn:hover .copy-tooltip,
.copy-text-box .copy-text-btn.is-copied .copy-tooltip {
    opacity: 1;
    visibility: visible;
}
</style>
@endpush

@push('scripts')
<script>
// Gerenciador global de cópia para a área de transferência
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.copy-text-btn');
        if (!button) {
            return;
        }

        const container = button.closest('.copy-text-box');
        const textToCopy = button.getAttribute('data-copy') || container.querySelector('.copy-text-content')?.innerText?.trim();

        if (!textToCopy) {
            return;
        }

        const tooltip = button.querySelector('.copy-tooltip');

        navigator.clipboard.writeText(textToCopy).then(function () {
            button.classList.add('is-copied');
            if (tooltip) {
                tooltip.textContent = 'Copiado!';
            }

            clearTimeout(button._copyTimeout);
            button._copyTimeout = setTimeout(function () {
                button.classList.remove('is-copied');
                if (tooltip) {
                    tooltip.textContent = 'Copiar';
                }
            }, 2000);
        }).catch(function (error) {
            console.error('Erro ao copiar para o clipboard:', error);
        });
    });
});
</script>
@endpush
@endonce
