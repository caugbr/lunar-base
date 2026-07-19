@props([
    'id' => 'mediaEdit-' . Str::random(6),
    'csrfToken' => csrf_token(),
])

<div
    x-data="mediaEdit('{{ $id }}', '{{ $csrfToken }}')"
    @media:edit.window="if ($event.detail?.item) openModal($event.detail.item)"
    x-show="open"
    x-cloak
    class="modal-overlay"
>
    <!-- Overlay (💡 Adicionado x-transition de opacidade) -->
    <div
        x-show="open"
        x-transition.opacity.duration.250ms
        @click="closeModal()"
        class="modal-backdrop"
    ></div>

    <!-- Modal (💡 Adicionado x-transition padrão: fade + escala suave) -->
    <div
        x-show="open"
        x-transition.duration.250ms
        @click.stop
        class="modal-box"
    >
        <!-- Header -->
        <div class="modal-header">
            <h3>Editar Metadados</h3>
            <button type="button" @click="closeModal()" class="modal-close">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-body">
            <!-- Preview + Formulário -->
            <div class="edit-layout">
                <!-- Preview -->
                <div class="edit-preview">
                    <div class="preview-frame">
                        <template x-if="media?.is_image">
                            <img :src="media?.url" :alt="media?.alt" class="preview-image">
                        </template>
                        <template x-if="!media?.is_image">
                            <div class="preview-placeholder">
                                <x-lucide-file class="lucid-icon" />
                                <span>Arquivo</span>
                            </div>
                        </template>
                    </div>
                    <p class="preview-size" x-text="media?.size_formatted"></p>

                    {{-- VÍNCULO VIA MEDIAABLE (galeria) --}}
                    <template x-if="media?.linked_to">
                        <div class="linked-info">
                            <div class="linked-info-label">
                                <x-lucide-link class="lucid-icon" style="width:1rem;height:1rem;" />
                                <span>Na galeria</span>
                            </div>
                            <div class="linked-info-content">
                                <span class="linked-info-type" x-text="'[' + media.linked_to.type + ']'"></span>
                                <template x-if="media.linked_to.url">
                                    <a :href="media.linked_to.url" class="linked-info-title"
                                    x-text="media.linked_to.title" target="_blank"></a>
                                </template>
                                <template x-if="!media.linked_to.url">
                                    <span class="linked-info-title" x-text="media.linked_to.title"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- VÍNCULO VIA THUMBNAIL --}}
                    <template x-if="media?.thumbnail_of">
                        <div class="linked-info linked-info--thumb">
                            <div class="linked-info-label">
                                <x-lucide-image class="lucid-icon" style="width:1rem;height:1rem;" />
                                <span>Usada como thumbnail</span>
                            </div>
                            <div class="linked-info-content">
                                <span class="linked-info-type" x-text="'[' + media.thumbnail_of.type + ']'"></span>
                                <template x-if="media.thumbnail_of.url">
                                    <a :href="media.thumbnail_of.url" class="linked-info-title"
                                    x-text="media.thumbnail_of.title" target="_blank"></a>
                                </template>
                                <template x-if="!media.thumbnail_of.url">
                                    <span class="linked-info-title" x-text="media.thumbnail_of.title"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- AVISO: nenhum vínculo --}}
                    <template x-if="!media?.linked_to && !media?.thumbnail_of">
                        <div class="linked-info linked-info--orphan">
                            <div class="linked-info-label">
                                <x-lucide-unlink class="lucid-icon" style="width:1rem;height:1rem;" />
                                <span>Imagem sem vínculo</span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Campos -->
                <div class="edit-fields">
                    <div x-show="error" class="error-message" x-text="error"></div>
                    <div x-show="success" class="success-message" x-text="success"></div>

                    <div class="form-group">
                        <label for="media-name">Nome do arquivo</label>
                        <input type="text" id="media-name" x-model="media.name" class="form-input" placeholder="nome-do-arquivo.jpg">
                    </div>

                    <div class="form-group">
                        <label for="media-alignment">Alinhamento</label>
                        <select id="media-alignment" x-model="media.meta.alignment" class="form-input">
                            <option value="none">Sem alinhamento definido</option>
                            <option value="left">Esquerda</option>
                            <option value="center">Centro</option>
                            <option value="right">Direita</option>
                            <option value="float-left">Esquerda (dentro do texto)</option>
                            <option value="float-right">Direita (dentro do texto)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="media-alt">Texto alternativo (SEO)</label>
                        <input type="text" id="media-alt" x-model="media.alt" class="form-input" placeholder="Descreva a imagem para acessibilidade">
                    </div>

                    <div class="form-group">
                        <label for="media-caption">Legenda</label>
                        <textarea id="media-caption" x-model="media.caption" class="form-input" rows="3" placeholder="Legenda opcional"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" @click="closeModal()" :disabled="saving" class="admin-btn admin-btn-secondary">Cancelar</button>
            <button type="button" @click="save()" :disabled="saving || !media?.name?.trim()" class="admin-btn admin-btn-primary">
                <template x-if="saving">
                    <x-lucide-loader class="lucid-icon animate-spin" />
                </template>
                <span x-text="saving ? 'Salvando...' : 'Salvar Alterações'"></span>
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    /* ===== OVERLAY ===== */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 50;
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop {
        position: fixed; inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    /* ===== MODAL BOX ===== */
    .modal-box {
        position: relative;
        width: 100%; max-width: 56rem;
        background: white; border-radius: 0.75rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        display: flex; flex-direction: column;
        max-height: 90vh;
    }

    /* ===== HEADER ===== */
    .modal-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;
    }
    .modal-header h3 {
        font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0;
    }
    .modal-close {
        background: none; border: none; padding: 0.25rem;
        color: #9ca3af; cursor: pointer; transition: color 0.2s;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-close:hover { color: #374151; }

    /* ===== BODY ===== */
    .modal-body {
        padding: 1.5rem; overflow-y: auto; flex: 1;
    }

    /* ===== LAYOUT EDIT (preview + fields) ===== */
    .edit-layout {
        display: flex; flex-direction: column; gap: 1.5rem;
    }
    @media (min-width: 768px) {
        .edit-layout { flex-direction: row; }
    }

    /* ===== PREVIEW ===== */
    .edit-preview {
        flex-shrink: 0; width: 100%;
    }
    @media (min-width: 768px) {
        .edit-preview { width: 33.333%; }
    }
    .preview-frame {
        background: #f9fafb; border: 1px solid #e5e7eb;
        border-radius: 0.5rem; overflow: hidden;
        aspect-ratio: 1 / 1;
        display: flex; align-items: center; justify-content: center;
    }
    .preview-image {
        width: 100%; height: 100%; object-fit: cover;
    }
    .preview-placeholder {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: #6b7280; gap: 0.5rem;
    }
    .preview-placeholder .lucid-icon { width: 2.5rem; height: 2.5rem; }
    .preview-placeholder span { font-size: 0.75rem; }
    .preview-size {
        margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280; text-align: center;
    }

    .linked-info {
        margin-top: 2rem;
        padding: 0.65rem;
        border: 1px solid var(--color-border);
        background-color: var(--color-bg-dark);
        color: var(--color-text);
        border-radius: 8px;
    }
    .linked-info-label {
        font-weight: 600;
    }
    .linked-info-label .lucid-icon {
        margin-right: 0.5rem;
    }
    .linked-info-content {
        margin-top: 0.75rem;
    }
    .linked-info-type {
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .linked-info-title {
        color: var(--color-text-muted);
        text-decoration: none;
        font-weight: 600;
    }

    /* ===== FIELDS ===== */
    .edit-fields {
        flex: 1; display: flex; flex-direction: column; gap: 1rem;
    }
    .error-message {
        padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca;
        color: #991b1b; border-radius: 0.375rem; font-size: 0.875rem;
    }
    .success-message {
        padding: 0.75rem; background: #f0fdf4; border: 1px solid #bbf7d0;
        color: #166534; border-radius: 0.375rem; font-size: 0.875rem;
    }

    /* ===== FOOTER ===== */
    .modal-footer {
        padding: 1rem 1.5rem; background: #f9fafb;
        border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
// O script JS permanece exatamente igual ao seu original.
function mediaEdit(id, csrf) {
    return {
        open: false,
        saving: false,
        error: null,
        success: null,
        media: {
            id: null,
            name: '',
            alt: '',
            caption: '',
            url: '',
            thumbnail_url: '',
            is_image: false,
            size_formatted: '',
            meta: { alignment: 'none' }
        },
        csrfToken: csrf,
        modalId: id,

        openModal(item) {
            const itemMeta = item.meta || {};
            const resolvedMeta = { alignment: 'none', ...itemMeta };

            this.media = {
                ...this.media,
                ...item,
                meta: resolvedMeta
            };
            this.error = null;
            this.success = null;
            this.open = true;
        },

        closeModal() {
            this.open = false;
        },

        async save() {
            if (!this.media?.id || !this.media?.name?.trim()) return;

            this.saving = true;
            this.error = null;
            this.success = null;

            try {
                const response = await fetch(`/admin/media/${this.media.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        name: this.media.name.trim(),
                        alt: this.media.alt?.trim() || null,
                        caption: this.media.caption?.trim() || null,
                        meta: {
                            alignment: this.media.meta?.alignment || 'none'
                        }
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.success = 'Metadados atualizados com sucesso!';
                    setTimeout(() => this.closeModal(), 1200);
                    window.dispatchEvent(new CustomEvent('media:updated'));
                } else {
                    this.error = data.message || 'Erro ao salvar. Verifique os campos.';
                }
            } catch (err) {
                console.error('Erro ao atualizar mídia:', err);
                this.error = 'Erro de conexão. Tente novamente.';
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
