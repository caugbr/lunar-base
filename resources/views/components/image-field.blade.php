@props([
    'name',
    'value' => null,          // ID da mídia (modal) ou path/valor atual
    'url' => null,            // URL da imagem inicial para preview
    'label' => null,
    'id' => null,
    'upload_mode' => 'modal', // 'modal' | 'input'
    'context' => null,        // Identificador de contexto para o modal
    'uploaderId' => 'mainUploader',
])

@php
    $inputId = $id ?? $name;
    $context = $context ?? $name;
    $initialUrl = old($name . '_url', $url ?? (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) ? $value : ''));
@endphp

<div class="image-field-wrapper"
     x-data="imageFieldManager({
         id: @js(old($name, $value)),
         url: @js($initialUrl),
         context: @js($context),
         uploadMode: @js($upload_mode)
     })"
     @if($upload_mode === 'modal')
         @media:inserted.window="handleMediaInserted($event.detail)"
     @endif
>
    @if($label)
        <label for="{{ $inputId }}">{{ $label }}</label>
    @endif

    <div class="thumbnail-selector">
        {{-- ================= PREVIEW UNIFICADO ================= --}}
        <div class="thumbnail-preview"
             :class="{ 'has-image': mediaUrl }"
             @click="!mediaUrl && handlePreviewClick()">

            {{-- Imagem de Preview --}}
            <template x-if="mediaUrl">
                <img :src="mediaUrl" alt="Preview" class="preview-image" @click="handlePreviewClick()">
            </template>

            {{-- Placeholder quando vazio --}}
            <template x-if="!mediaUrl">
                <div class="preview-placeholder">
                    <x-lucide-image class="lucid-icon" />
                    <span>Clique para selecionar</span>
                </div>
            </template>

            {{-- Botão de Remover Imagem (X) --}}
            <button type="button"
                    x-show="mediaUrl"
                    @click.stop="clearMedia()"
                    class="preview-remove"
                    title="Remover imagem">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

        {{-- ================= CONTROLES DO MODO MODAL ================= --}}
        @if($upload_mode === 'modal')
            <div class="thumbnail-actions" x-show="!mediaUrl">
                <button type="button"
                        @click="openUploader()"
                        class="admin-btn admin-btn-secondary">
                    <x-lucide-upload class="lucid-icon" /> Upload
                </button>

                <button type="button"
                        @click="openSelector()"
                        class="admin-btn admin-btn-secondary">
                    <x-lucide-library class="lucid-icon" /> Biblioteca
                </button>
            </div>

            <input type="hidden" name="{{ $name }}" :value="mediaId" id="{{ $inputId }}">

        {{-- ================= CONTROLES DO MODO INPUT ================= --}}
        @else
            <div class="thumbnail-input-area" @change="handleFileChange($event)">
                <x-upload-area name="{{ $name }}" id="{{ $inputId }}" />
            </div>

            <input type="hidden" name="remove_{{ $name }}" :value="isRemoved ? 1 : 0">
        @endif
    </div>

    <small class="image-help form-help">
        @if($upload_mode === 'modal')
            Selecione uma imagem da biblioteca ou envie um novo arquivo.
        @else
            Selecione um arquivo para enviar ou clique no <strong>X</strong> para remover a imagem atual.
        @endif
    </small>
</div>

{{-- Modais carregados SOMENTE se o modo for modal --}}
@if($upload_mode === 'modal')
    @once
    <x-media.grid-modal />
    <x-media.upload-modal id="{{ $uploaderId }}" folder="uploads" accept="image/*,application/pdf" :max-size="10240" />
    @endonce
@endif

{{-- Script ÚNICO e universal: carregado apenas uma vez por página --}}
@once
@push('scripts')
<script>
    function imageFieldManager(config = {}) {
        return {
            mediaId: config.id || null,
            mediaUrl: config.url || '',
            context: config.context || 'thumbnail',
            uploadMode: config.uploadMode || 'modal',
            isRemoved: false,

            openSelector() {
                window.dispatchEvent(new CustomEvent('modal-open', {
                    detail: { id: 'selectorModal', context: this.context }
                }));
            },

            openUploader() {
                window.dispatchEvent(new CustomEvent('media:upload-open', {
                    detail: { id: 'mainUploader', context: this.context }
                }));
            },

            handlePreviewClick() {
                if (this.uploadMode === 'modal') {
                    this.openSelector();
                } else {
                    // Modo input: aciona o file picker do navegador
                    const fileInput = this.$el.querySelector('input[type="file"]');
                    if (fileInput) fileInput.click();
                }
            },

            handleMediaInserted(detail) {
                // Apenas atualiza se o retorno do modal for direcionado a este campo
                if (detail.source === this.context) {
                    this.mediaId = detail.media.id;
                    this.mediaUrl = detail.media.thumbnail_url || detail.media.url;
                    this.isRemoved = false;

                    window.dispatchEvent(new CustomEvent('modal-close', {
                        detail: { id: 'selectorModal' }
                    }));
                }
            },

            handleFileChange(event) {
                const file = event.target.files?.[0];
                if (file) {
                    // Libera memória de blob anterior se existir
                    if (this.mediaUrl && this.mediaUrl.startsWith('blob:')) {
                        URL.revokeObjectURL(this.mediaUrl);
                    }
                    this.mediaUrl = URL.createObjectURL(file);
                    this.isRemoved = false;
                }
            },

            clearMedia() {
                this.mediaId = null;

                if (this.mediaUrl && this.mediaUrl.startsWith('blob:')) {
                    URL.revokeObjectURL(this.mediaUrl);
                }
                this.mediaUrl = '';
                this.isRemoved = true;

                // Limpa o input de arquivo físico caso exista (modo input)
                const fileInput = this.$el.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';
            }
        }
    }

    window.addEventListener('media:uploaded', (e) => {
        // 1. Fecha o modal de upload
        window.dispatchEvent(new CustomEvent('modal-close', {
            detail: { id: 'mainUploader' }
        }));

        // 2. Já insere a imagem direto no campo que pediu o upload!
        window.dispatchEvent(new CustomEvent('media:inserted', {
            detail: {
                media: e.detail.media,
                source: e.detail.source
            }
        }));
    });
</script>
@endpush

@push('styles')
<style>
/* Container da linha */
.thumbnail-selector {
    display: flex;
    align-items: stretch;
    gap: 1rem;
    height: 130px;
    width: 100%;
}

/* ================= LADO ESQUERDO: QUADRADO 130x130 ================= */
.thumbnail-preview {
    width: 130px;
    height: 130px;
    flex: 0 0 130px; /* Trava o tamanho exato de 130px, sem esticar nem encolher */
    position: relative;
    border: 2px dashed var(--color-border, rgba(255, 255, 255, 0.15));
    border-radius: 8px;
    background: var(--color-bg-dark, #0f172a);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease;
}

.thumbnail-preview:hover {
    border-color: var(--color-primary, #3b82f6);
}

.thumbnail-preview.has-image {
    border-style: solid;
    border-color: var(--color-border, rgba(255, 255, 255, 0.12));
}

/* Imagem carregada cobrindo o quadrado perfeitamente */
.thumbnail-preview img.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Placeholder quando NÃO há imagem */
.thumbnail-preview .preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-align: center;
    padding: 8px;
    color: var(--color-text-muted, #94a3b8);
    pointer-events: none;
}

.thumbnail-preview .preview-placeholder svg,
.thumbnail-preview .preview-placeholder .lucid-icon {
    width: 32px;
    height: 32px;
    opacity: 0.7;
}

.thumbnail-preview .preview-placeholder span {
    font-size: 0.7rem;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

/* Botão de excluir imagem (X) */
.thumbnail-preview .preview-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
    z-index: 10;
}

.thumbnail-preview .preview-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.thumbnail-preview .preview-remove svg,
.thumbnail-preview .preview-remove .lucid-icon {
    width: 14px;
    height: 14px;
}

/* ================= LADO DIREITO: ESTICA ATÉ O FINAL ================= */
.thumbnail-actions,
.thumbnail-input-area {
    flex: 1; /* Ocupa todo o restante da largura disponível */
    min-width: 0;
    height: 100%;
    display: flex;
    align-items: center;
}

/* Botões do Modo Modal */
.thumbnail-actions {
    gap: 0.75rem;
    /* padding: 1rem;
    border: 1px solid var(--color-border, rgba(255, 255, 255, 0.08)); */
    border-radius: 8px;
    background: var(--color-bg-card, rgba(255, 255, 255, 0.02));
    justify-content: flex-start;
    align-items: flex-end;
}

/* Modo Upload Input / Upload Area */
.thumbnail-input-area {
    align-items: stretch;
}

.thumbnail-input-area > *,
.thumbnail-input-area .upload-area {
    width: 100%;
    height: 100%;
}

/* Responsividade para telas muito estreitas (ex: celular) */
@media (max-width: 540px) {
    .thumbnail-selector {
        flex-direction: column;
        height: auto;
    }

    .thumbnail-preview {
        width: 100%;
        height: 140px;
        flex: none;
    }

    .thumbnail-actions,
    .thumbnail-input-area {
        width: 100%;
        min-height: 80px;
    }
}
</style>
@endpush
@endonce
