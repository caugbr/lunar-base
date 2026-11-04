@props([
    'name',
    'value' => null,
    'label' => null,
    'canClear' => true,
    'required' => false,
    'help' => null,
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $currentValue = old($dotName, $value);

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
@endphp

<div class="form-group icon-selector-component" id="icon_selector_wrapper_{{ $id }}">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </label>
    @endif

    <div class="icon-selector-field">
        <div class="icon-preview-box" id="{{ $id }}_preview">
            @if($currentValue)
                <x-dynamic-component :component="'lucide-' . $currentValue" class="lucid-icon" />
            @else
                <x-lucide-help-circle class="lucid-icon placeholder" />
            @endif
        </div>

        <input
            type="text"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ $currentValue }}"
            placeholder="Nenhum ícone selecionado"
            autocomplete="off"
            readonly
            @if($required) required @endif
            {{ $attributes->class(['form-control', 'icon-text-input', 'is-invalid' => $hasError]) }}
        >

        @if($canClear)
            <button type="button" class="admin-btn admin-btn-secondary clear-btn" title="Limpar ícone">
                <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
            </button>
        @endif

        <button type="button" class="admin-btn admin-btn-secondary choose-btn" title="Abrir catálogo">
            <x-lucide-library class="lucid-icon" /> Escolher
        </button>
    </div>

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror

    {{-- Modal de seleção --}}
    <div class="icon-modal-overlay">
        <div class="icon-modal-backdrop"></div>

        <div class="icon-modal-box lg">
            <div class="icon-modal-header">
                <h3>Selecionar ícone</h3>
                <button type="button" class="icon-modal-close" aria-label="Fechar">&times;</button>
            </div>

            <div class="icon-modal-body">
                <div class="icon-search-wrapper">
                    <input
                        type="text"
                        class="form-control search-input"
                        placeholder="Digite para filtrar os ícones (ex: home, arrow, check)..."
                        autocomplete="off"
                        spellcheck="false"
                    >
                </div>

                <div class="icon-grid-scroll">
                    <div class="icon-selector-grid">
                        @foreach($icons as $icon)
                            <button
                                type="button"
                                class="icon-grid-item {{ $currentValue === $icon ? 'active' : '' }}"
                                data-icon="{{ $icon }}"
                                title="{{ $icon }}"
                            >
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
<link rel="stylesheet" href="{{ asset('css/admin/components/picker/icon.css') }}">
@endpush

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    document.querySelectorAll('.icon-selector-component').forEach(wrapper => {
        const input = wrapper.querySelector('input.icon-text-input');
        const preview = wrapper.querySelector('.icon-preview-box');
        const chooseBtn = wrapper.querySelector('.choose-btn');
        const clearBtn = wrapper.querySelector('.clear-btn');
        const modal = wrapper.querySelector('.icon-modal-overlay');
        const backdrop = wrapper.querySelector('.icon-modal-backdrop');
        const closeBtn = wrapper.querySelector('.icon-modal-close');
        const searchInput = wrapper.querySelector('.search-input');
        const gridItems = wrapper.querySelectorAll('.icon-grid-item');

        const closeModal = () => {
            if (modal) modal.classList.remove('is-open');
        };

        if (chooseBtn && modal) {
            chooseBtn.addEventListener('click', () => {
                modal.classList.add('is-open');
                if (searchInput) {
                    searchInput.value = '';
                    gridItems.forEach(item => item.style.display = '');
                    setTimeout(() => searchInput.focus(), 60);
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                preview.innerHTML = '';
                gridItems.forEach(i => i.classList.remove('active'));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();
                gridItems.forEach(item => {
                    const iconName = item.dataset.icon;
                    item.style.display = (query === '' || iconName.includes(query)) ? '' : 'none';
                });
            });
        }

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
