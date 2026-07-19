@props([
    'name',
    'value' => null,
    'label' => null,
    'id' => null,
    'can_clear' => true
])

@php
    $id = $id ?? $name;
    $currentValue = old($name, $value);

    $factory = app(\BladeUI\Icons\Factory::class);

    $sets = method_exists($factory, 'sets')
        ? $factory->sets()
        : (method_exists($factory, 'all') ? $factory->all() : []);

    $lucideSet = collect($sets)->first(function ($set) {
        if (is_array($set)) {
            return ($set['prefix'] ?? '') === 'lucide';
        }
        return is_object($set) && method_exists($set, 'prefix') && $set->prefix() === 'lucide';
    });

    $excludedIcons = [
        'chrome', 'chromium', 'codepen', 'codesandbox', 'dribbble', 'facebook', 'figma', 'framer',
        'github', 'gitlab', 'instagram', 'linkedin', 'pocket', 'rail-symbol', 'slack', 'trello',
        'twitch', 'twitter', 'youtube', 'mouse-pointer-square', 'circle-euro-sign'
    ];

    $icons = [];
    if ($lucideSet) {
        $paths = is_array($lucideSet)
            ? ($lucideSet['paths'] ?? [])
            : (is_object($lucideSet) && method_exists($lucideSet, 'paths') ? $lucideSet->paths() : []);

        foreach ($paths as $path) {
            if (\Illuminate\Support\Facades\File::exists($path)) {
                $files = \Illuminate\Support\Facades\File::allFiles($path);
                foreach ($files as $file) {
                    $iconName = $file->getBasename('.svg');

                    if (!in_array($iconName, $excludedIcons)) {
                        $icons[] = $iconName;
                    }
                }
            }
        }
        sort($icons);
    }

    $wrapperId = 'icon_selector_wrapper_' . $id;
@endphp

<div class="icon-selector-component" id="{{ $wrapperId }}">
    @if($label)
        <label for="{{ $id }}" class="field-label">{{ $label }}</label>
    @endif

    <div class="icon-selector-field">
        <div class="icon-preview-box" id="{{ $id }}_preview">
            @if($currentValue)
                <x-dynamic-component :component="'lucide-' . $currentValue" class="lucid-icon" />
            @else
                <x-lucide-help-circle class="lucid-icon placeholder" />
            @endif
        </div>

        <input type="text"
               name="{{ $name }}"
               id="{{ $id }}"
               value="{{ $currentValue }}"
               class="form-input"
               placeholder="Nenhum ícone selecionado"
               autocomplete="off"
               readonly>

        @if($can_clear)
        <button type="button" class="admin-btn admin-btn-secondary clear-btn">
            <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
        </button>
        @endif

        <button type="button" class="admin-btn admin-btn-secondary choose-btn">
            <x-lucide-library class="lucid-icon" /> Escolher
        </button>
    </div>

    {{-- Modal de Seleção (Popup) - 💡 Removido o display:none em linha --}}
    <div class="modal-overlay">
        <div class="modal-backdrop"></div>

        <div class="modal-box lg" style="max-height: 80vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <h3>Selecionar Ícone Lucide</h3>
                <button type="button" class="modal-close" aria-label="Fechar">&times;</button>
            </div>

            <div class="modal-body" style="overflow: hidden; display: flex; flex-direction: column; flex: 1;">
                <div class="form-group" style="margin-bottom: 1.5rem; flex-shrink: 0;">
                    <input type="text"
                           class="form-input search-input"
                           placeholder="Digite para filtrar os ícones (ex: home, arrow, check)..."
                           autocomplete="off"
                           spellcheck="false">
                </div>

                <div class="icon-grid-scroll">
                    <div class="icon-selector-grid">
                        @foreach($icons as $icon)
                            <button type="button"
                                    class="icon-grid-item {{ $currentValue === $icon ? 'active' : '' }}"
                                    data-icon="{{ $icon }}"
                                    title="{{ $icon }}">
                                <span class="icon-graphic">
                                    <i data-lucide="{{ $icon }}"></i>
                                </span>
                                <span class="icon-title">{{ $icon }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@once
@push('styles')
<style>
    .icon-selector-field {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }
    .icon-selector-field input[readonly] {
        background-color: #f9fafb;
        cursor: default;
    }
    .icon-preview-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid var(--color-border, #ddd);
        border-radius: 6px;
        background: rgba(0,0,0,0.02);
        flex-shrink: 0;
        color: var(--color-primary, #6b7280);
    }
    .icon-preview-box .lucid-icon {
        width: 20px;
        height: 20px;
    }

    /* --- ESTILOS DO MODAL / POPUP COM TRANSIÇÃO --- */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;

        /* 💡 ESTADO FECHADO (Invisível) */
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    /* 💡 ESTADO ABERTO (Transição ativada) */
    .modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }

    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }

    .modal-box {
        position: relative;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 600px;
        z-index: 10000;
        border: 1px solid #e5e7eb;
        overflow: hidden;

        /* 💡 ESCALA/OPACIDADE PADRÃO (Início do efeito) */
        transform: scale(0.95);
        opacity: 0;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    /* 💡 ESCALA/OPACIDADE QUANDO ABERTO */
    .modal-overlay.is-open .modal-box {
        transform: scale(1);
        opacity: 1;
    }

    .modal-box.lg {
        max-width: 800px;
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #ffffff;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
    }
    .modal-close {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #9ca3af;
        transition: color 0.15s ease;
        padding: 0.25rem;
        line-height: 1;
    }
    .modal-close:hover {
        color: #374151;
    }
    .modal-body {
        padding: 1.5rem;
        background-color: #ffffff;
    }

    /* Grid de Seleção no Modal */
    .icon-grid-scroll {
        overflow-y: auto;
        flex: 1;
        padding-right: 0.5rem;
    }
    .icon-selector-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.75rem;
    }
    .icon-grid-item {
        background: white;
        border: 1px solid var(--color-border, #e5e7eb);
        border-radius: 8px;
        padding: 0.75rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .icon-grid-item:hover {
        border-color: var(--color-primary, #718096);
        background-color: var(--color-border-hover, #f8f9fa);
    }
    .icon-grid-item.active {
        border-color: var(--color-primary-dark, #3b82f6);
        background-color: var(--color-glow, #eff6ff);
        color: var(--color-primary-dark, #3b82f6);
    }
    .icon-graphic {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-muted);
    }
    .icon-grid-item.active .icon-graphic {
        color: var(--color-primary-dark);
    }
    .icon-graphic svg {
        width: 24px;
        height: 24px;
    }
    .icon-title {
        font-size: 0.725rem;
        color: var(--color-text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    document.querySelectorAll('.icon-selector-component').forEach(wrapper => {
        const input = wrapper.querySelector('input[type="text"]');
        const preview = wrapper.querySelector('.icon-preview-box');
        const chooseBtn = wrapper.querySelector('.choose-btn');
        const modal = wrapper.querySelector('.modal-overlay');
        const backdrop = wrapper.querySelector('.modal-backdrop');
        const closeBtn = wrapper.querySelector('.modal-close');
        const searchInput = wrapper.querySelector('.search-input');
        const gridItems = wrapper.querySelectorAll('.icon-grid-item');

        // 1. Abrir o Modal
        if (chooseBtn && modal) {
            chooseBtn.addEventListener('click', () => {
                // 💡 Modificado: Agora adicionamos a classe 'is-open'
                modal.classList.add('is-open');

                if (searchInput) {
                    searchInput.value = '';
                    gridItems.forEach(item => item.style.display = '');
                    setTimeout(() => searchInput.focus(), 80);
                }
            });

            const clearBtn = wrapper.querySelector('.clear-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    input.value = '';
                    preview.innerHTML = '';
                });
            }
        }

        // 2. Fechar o Modal
        const closeModal = () => {
            // 💡 Modificado: Agora removemos a classe 'is-open'
            if (modal) modal.classList.remove('is-open');
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            // 💡 Modificado: Verificação da classe 'is-open' ativa
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // 3. Filtrar os ícones em Tempo Real
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();

                gridItems.forEach(item => {
                    const iconName = item.dataset.icon;
                    if (query === '' || iconName.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // 4. Selecionar o Ícone
        gridItems.forEach(item => {
            item.addEventListener('click', () => {
                const icon = item.dataset.icon;

                if (input) {
                    input.value = icon;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }

                gridItems.forEach(i => i.classList.toggle('active', i === item));

                if (preview) {
                    const svgMarkup = item.querySelector('.icon-graphic').innerHTML;
                    preview.innerHTML = svgMarkup;

                    const previewSvg = preview.querySelector('svg');
                    if (previewSvg) {
                        previewSvg.setAttribute('class', 'lucid-icon');
                    }
                }

                closeModal();
            });
        });
    });
});
</script>
@endpush
@endonce
