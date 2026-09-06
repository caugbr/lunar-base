@props([
    'name' => 'content',
    'jsonName' => 'content_json',
    'value' => '',
    'json' => null,
    'forceType' => null
])

@php
    $isTiptap = $forceType === 'tiptap' || (!empty($json) || empty($value));
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

            // 1. Pega o HTML atual do TinyMCE
            const currentHtml = (typeof tinymce !== 'undefined' && tinymce.activeEditor)
                ? tinymce.activeEditor.getContent()
                : (document.getElementById('content')?.value || '');

            // 2. Destrói o TinyMCE e remove os botões legados
            if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                tinymce.activeEditor.destroy();
            }

            const wrapper = document.querySelector('.lunar-editor-wrapper');
            if (!wrapper) return;

            // 3. Substitui o HTML pelo container do Tiptap
            wrapper.innerHTML = `
                <div
                    id="lunar-tiptap-app"
                    data-initial-html="${encodeURIComponent(currentHtml)}"
                ></div>
                <input type="hidden" name="{{ $jsonName }}" id="lunar_content_json" value="">
                <input type="hidden" name="{{ $name }}" id="lunar_content_html" value="">
            `;

            // 4. Injeta o CSS do Tiptap dinamicamente
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

            // 5. Função para carregar scripts em sequência
            const loadScript = (src) => {
                return new Promise((resolve) => {
                    const script = document.createElement('script');
                    script.type = 'module';
                    script.src = src;
                    script.onload = resolve;
                    document.body.appendChild(script);
                });
            };

            // 6. Carrega o script do Tiptap e Plugins
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
