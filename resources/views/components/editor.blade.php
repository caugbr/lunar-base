@props([
    'name' => 'content',
    'jsonName' => 'content_json',
    'value' => '',
    'json' => null,
    'forceType' => null
])

@php
    $isTiptap = $forceType === 'tiptap' || (!empty($json) || empty($value));

    // Lê as configurações do grupo 'editor' via helper nativo
    $editorSettings = settingsGroup('editor');

    // Converte a string de cores separadas por vírgula em um array limpo
    $rawPalette = $editorSettings['color_palette'] ?? '#0f172a, #64748b, #ef4444, #f97316, #eab308, #22c55e, #3b82f6, #a855f7, #ec4899';
    $colorPalette = is_array($rawPalette)
        ? $rawPalette
        : array_values(array_filter(array_map('trim', explode(',', $rawPalette))));

    // Garante que a lista de ferramentas ativas seja um array
    $rawTools = $editorSettings['toolbar_tools'] ?? [];
    $toolbarTools = is_array($rawTools)
        ? $rawTools
        : (is_string($rawTools) ? array_values(array_filter(array_map('trim', explode(',', $rawTools)))) : []);

    // Monta o pacote de configuração para o Vue
    $editorConfig = [
        'tools'              => $toolbarTools,
        'colors'             => $colorPalette,
        'allow_custom_color' => (bool) ($editorSettings['allow_custom_color'] ?? true),
    ];

    $editorConfigJson = json_encode($editorConfig);
@endphp

<div class="lunar-editor-wrapper">
    @if($isTiptap)
        {{-- ================================================= --}}
        {{-- 1. NOVO EDITOR TIPTAP                             --}}
        {{-- ================================================= --}}
        <div
            id="lunar-tiptap-app"
            data-initial-json="{{ is_array($json) ? json_encode($json) : ($json ?? '') }}"
            data-initial-html="{{ $value }}"
            data-editor-config="{{ $editorConfigJson }}"
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
</div>
