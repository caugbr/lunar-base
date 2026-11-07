@props([
    'name' => 'content',
    'jsonName' => 'content_json',
    'value' => '',
    'json' => null,
    'forceType' => null,
    'toolbarTop' => 100,
    'minHeight' => 250,
    'excludeTools' => null,       // Remove ferramentas específicas (ex: 'media_upload,media_library')
    'tools' => null,              // Sobrescreve as ferramentas (string ou array)
    'preset' => null,             // Atalhos prontos: 'email', 'simple'
    'colors' => null,             // Sobrescreve as cores
    'allowCustomColor' => null,   // bool
    'groupInlineTools' => null,   // bool
    'compactDropdowns' => null,   // bool
    'stickyToolbar' => null,      // bool
])

@php
    $isTiptap = $forceType === 'tiptap' || (!empty($json) || empty($value));

    // 1. Lê as configurações globais do banco como base
    $editorSettings = settingsGroup('editor');

    // 2. Presets prontos para cenários comuns
    $presets = [
        'email' => [
            'heading', 'bold', 'italic', 'underline', 'strike',
            'text_color', 'link', 'bullet_list', 'ordered_list',
            'blockquote', 'hr', 'history'
        ],
        'simple' => [
            'bold', 'italic', 'underline', 'link',
            'bullet_list', 'ordered_list', 'history'
        ],
    ];

    // 3. Resolve a lista de ferramentas (Prop > Preset > Configuração Global)
    if (!empty($tools)) {
        $rawTools = $tools;
    } elseif ($preset && isset($presets[$preset])) {
        $rawTools = $presets[$preset];
    } else {
        $rawTools = $editorSettings['toolbar_tools'] ?? [];
    }

    $toolbarTools = is_array($rawTools)
        ? $rawTools
        : (is_string($rawTools) ? array_values(array_filter(array_map('trim', explode(',', $rawTools)))) : []);

    // 4. Se 'excludeTools' foi informado, remove apenas essas ferramentas da lista
    if (!empty($excludeTools)) {
        $excluded = is_array($excludeTools)
            ? $excludeTools
            : array_values(array_filter(array_map('trim', explode(',', $excludeTools))));
        $toolbarTools = array_values(array_diff($toolbarTools, $excluded));
    }

    // 5. Paleta de cores (Prop > Global > Fallback)
    $rawPalette = $colors ?? ($editorSettings['color_palette'] ?? '#0f172a, #64748b, #ef4444, #f97316, #eab308, #22c55e, #3b82f6, #a855f7, #ec4899');
    $colorPalette = is_array($rawPalette)
        ? $rawPalette
        : array_values(array_filter(array_map('trim', explode(',', $rawPalette))));

    // 6. Flags booleanas (Prop > Global > Default)
    $editorConfig = [
        'tools'              => $toolbarTools,
        'colors'             => $colorPalette,
        'allow_custom_color' => (bool) ($allowCustomColor ?? ($editorSettings['allow_custom_color'] ?? true)),
        'group_inline_tools' => (bool) ($groupInlineTools ?? ($editorSettings['group_inline_tools'] ?? true)),
        'compact_dropdowns'  => (bool) ($compactDropdowns ?? ($editorSettings['compact_dropdowns'] ?? true)),
        'sticky_toolbar'     => (bool) ($stickyToolbar    ?? ($editorSettings['sticky_toolbar'] ?? true)),
    ];

    $editorConfigJson = json_encode($editorConfig);
@endphp

<script>
    window.LunarKnownBlocks = @json(\App\Services\EditorManager::editorBlocksMap());
</script>

<x-hook name="admin.editor_before_scripts" desc="Injeção prévia de scripts para o editor Tiptap" />

<div class="lunar-editor-wrapper">
    @if($isTiptap)
        {{-- ================================================= --}}
        {{-- 1. NOVO EDITOR TIPTAP                             --}}
        {{-- ================================================= --}}
        <div
            id="lunar-tiptap-app"
            data-initial-json="{{ is_array($json) ? json_encode($json) : ($json ?? '') }}"
            data-initial-html="{{ e($value) }}"
            data-editor-config="{{ $editorConfigJson }}"
            data-toolbar-top="{{ $toolbarTop }}"
            data-min-height="{{ $minHeight }}"
        ></div>

        <input type="hidden" name="{{ $jsonName }}" id="lunar_content_json" value="{{ is_array($json) ? json_encode($json) : $json }}">
        <input type="hidden" name="{{ $name }}" id="lunar_content_html" value="{{ $value }}">

        @push('styles')
            <link rel="stylesheet" href="{{ asset('css/tiptap-editor.css') }}">
            @foreach(\App\Services\EditorManager::getStyles() as $style)
                <link rel="stylesheet" href="{{ $style }}">
            @endforeach
        @endpush

        {{-- Scripts inline do Tiptap e Plugins --}}
        <script type="module" src="{{ asset('js/tiptap-editor.js') }}"></script>
        @foreach(\App\Services\EditorManager::getScripts() as $script)
            <script type="module" src="{{ $script }}"></script>
        @endforeach

    @else
        {{-- ================================================= --}}
        {{-- 2. EDITOR LEGADO TINYMCE                          --}}
        {{-- ================================================= --}}

        {{-- Faixa amigável com o botão de migração --}}
        <div class="legacy-editor-banner" style="margin-bottom: 14px; padding: 12px 16px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13.5px; color: #854d0e; font-weight: 500;">
                <x-lucide-triangle-alert class="lucid-icon" />
                Este conteúdo foi criado no editor clássico.
            </span>
            <button type="button" class="admin-btn admin-btn-primary" style="font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="window.convertToTiptap()">
                <span>Migrar para o Novo Editor de Blocos</span>
            </button>
        </div>

        <div class="editor-top">
            <label for="{{ $name }}">Conteúdo *</label>
            <div class="image-buttons">
                <button class="admin-btn admin-btn-secondary" type="button"
                        onclick="window.dispatchEvent(new CustomEvent('media:upload-open', { detail: { id: 'mainUploader', context: 'editor' } }))">
                    <x-lucide-upload class="lucid-icon" /> Upload de imagem
                </button>
                <button class="admin-btn admin-btn-secondary" type="button"
                        onclick="window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: 'selectorModal', context: 'editor' } }))">
                    <x-lucide-image class="lucid-icon" /> Inserir imagem
                </button>
            </div>
        </div>

        <div id="tiny-editor" class="tiny-editor"></div>
        <textarea name="{{ $name }}" id="content" rows="15" style="display: none;">{{ $value }}</textarea>

        <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ asset('js/tinymce-editor.js') }}"></script>

        {{-- LÓGICA DE MIGRAÇÃO INSTANTÂNEA PARA TIPTAP --}}
        <script>
        window.convertToTiptap = async function() {
            const confirmed = await Dialog.confirm('Deseja converter este conteúdo para o novo editor de blocos? O formato original será preservado até que você clique em Salvar.');
            if (!confirmed) {
                return;
            }

            // Pega o HTML atual do TinyMCE
            const currentHtml = (typeof tinymce !== 'undefined' && tinymce.activeEditor)
                ? tinymce.activeEditor.getContent()
                : (document.getElementById('content')?.value || '');

            // Destrói o TinyMCE e remove os botões legados
            if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                tinymce.activeEditor.destroy();
            }

            const wrapper = document.querySelector('.lunar-editor-wrapper');
            if (!wrapper) return;

            // Substitui o HTML pelo container do Tiptap com a configuração dinâmica injetada
            wrapper.innerHTML = `
                <div
                    id="lunar-tiptap-app"
                    data-initial-html="${encodeURIComponent(currentHtml)}"
                    data-editor-config="{{ $editorConfigJson }}"
                ></div>
                <input type="hidden" name="{{ $jsonName }}" id="lunar_content_json" value="">
                <input type="hidden" name="{{ $name }}" id="lunar_content_html" value="">
            `;

            // Injeta o CSS do Tiptap dinamicamente
            if (!document.querySelector('link[href*="tiptap-editor.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '{{ asset("css/tiptap-editor.css") }}';
                document.head.appendChild(link);
            }

            // Injeta CSS de plugins se houver
            @foreach(\App\Services\EditorManager::getStyles() as $style)
                if (!document.querySelector('link[href*="{{ $style }}"]')) {
                    const pluginLink = document.createElement('link');
                    pluginLink.rel = 'stylesheet';
                    pluginLink.href = '{{ $style }}';
                    document.head.appendChild(pluginLink);
                }
            @endforeach

            // Função para carregar scripts em sequência
            const loadScript = (src) => {
                return new Promise((resolve) => {
                    const script = document.createElement('script');
                    script.type = 'module';
                    script.src = src;
                    script.onload = resolve;
                    document.body.appendChild(script);
                });
            };

            // Carrega o script do Tiptap e Plugins
            loadScript('{{ asset("js/tiptap-editor.js") }}').then(() => {
                @foreach(\App\Services\EditorManager::getScripts() as $pluginScript)
                    loadScript('{{ $pluginScript }}');
                @endforeach
            });

            // Marca o formulário como alterado para proteção
            window.dispatchEvent(new CustomEvent('editor:dirty'));
        };
        </script>
    @endif
    <script src="{{ asset('js/editor-common.js') }}"></script>

    <x-hook name="admin.editor_scripts" desc="Injeção de scripts para o editor Tiptap" />
</div>
