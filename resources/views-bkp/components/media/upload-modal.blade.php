@props([
    'id' => 'mediaUpload-' . Str::random(6),
    'context' => null,
    'accept' => 'image/*',
    'maxSize' => 10240,
    'folder' => 'uploads',
])

<div
    x-data="uploadModal('{{ $id }}', '{{ $context }}', '{{ $accept }}', {{ $maxSize }}, '{{ $folder }}', '{{ csrf_token() }}')"
    @media:upload-open.window="if ($event.detail?.id === id) { open = true; currentContext = $event.detail.context || '{{ $context }}'; }"
    x-show="open"
    x-cloak
    class="modal-overlay"
>
    <!-- Overlay / Backdrop (💡 Adicionado x-transition de opacidade) -->
    <div
        x-show="open"
        x-transition.opacity.duration.250ms
        @click="close()"
        class="modal-backdrop"
    ></div>

    <!-- Modal Box (💡 Adicionado x-transition padrão: fade + escala suave) -->
    <div
        x-show="open"
        x-transition.duration.250ms
        @click.stop
        class="modal-box"
    >
        <!-- Header -->
        <div class="modal-header">
            <h3>Upload de Mídia</h3>
            <button @click="close()" class="modal-close">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-body">

            <!-- Estado 1: Selecionar Arquivo -->
            <div x-show="step === 'select'">
                <div
                    @click="$refs.fileInput.click()"
                    class="drop-zone"
                    :class="{ 'drop-zone-active': dragOver }"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="handleDrop($event); dragOver = false"
                >
                    <input type="file" x-ref="fileInput" :accept="accept" class="input-file-hidden" @change="selectFile($event.target.files[0])">
                    <x-lucide-upload class="lucid-icon drop-icon" />
                    <p class="drop-text">Clique ou arraste um arquivo</p>
                    <p class="drop-hint">Máx. <span x-text="formatSize(maxSize)"></span></p>
                </div>

                <!-- Arquivo selecionado -->
                <div x-show="selectedFile.name" class="selected-file">
                    <p><strong>Arquivo selecionado:</strong> <span x-text="selectedFile.name"></span></p>
                    <p class="file-meta"><span x-text="formatSize(selectedFile.size / 1024)"></span> • <span x-text="selectedFile.type"></span></p>
                </div>

                <p x-show="error" class="error-message" x-text="error"></p>
            </div>

            <!-- Estado 2: Upload em progresso -->
            <div x-show="step === 'uploading'" class="uploading-state">
                <x-lucide-loader class="lucid-icon spinner" />
                <p>Enviando <span x-text="selectedFile?.name"></span>... <span x-text="progress + '%'"></span></p>
                <div class="progress-bar"><div class="progress-fill" :style="`width: ${progress}%`"></div></div>
            </div>

            <!-- Estado 3: Metadados pós-upload -->
            <div x-show="step === 'metadata'">
                <div class="preview-card">
                    <img x-show="media?.is_image" :src="media?.url" class="preview-thumb">
                    <div x-show="!media?.is_image" class="preview-icon"><x-lucide-file class="lucid-icon" /></div>
                    <div class="preview-info">
                        <p class="preview-name" x-text="media?.name"></p>
                        <p class="preview-size" x-text="media?.size_formatted"></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>Texto alternativo</label>
                    <input type="text" x-model="media.alt" class="form-input">
                </div>
                <div class="form-group">
                    <label>Legenda</label>
                    <textarea x-model="media.caption" class="form-input" rows="2"></textarea>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button @click="resetSelection()" class="admin-btn admin-btn-secondary" x-show="selectedFile.name && step !== 'metadata'">Trocar</button>
            <button @click="close()" class="admin-btn admin-btn-secondary">
                <span x-text="step === 'metadata' ? 'Fechar' : 'Cancelar'"></span>
            </button>
            <button @click="confirmAndUpload()" class="admin-btn admin-btn-primary" x-show="selectedFile.name && step !== 'metadata'">Enviar</button>
            <button x-show="step === 'metadata'" @click="save()" class="admin-btn admin-btn-primary">Salvar</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak], .input-file-hidden { display: none !important; }

    .modal-overlay { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-box { position: relative; width: 100%; max-width: 28rem; background: white; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.15); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
    .modal-header h3 { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; }
    .modal-close { background: none; border: none; padding: 0.25rem; color: #9ca3af; cursor: pointer; transition: color 0.2s; display: flex; align-items: center; justify-content: center; }
    .modal-close:hover { color: #374151; }
    .modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }

    .drop-zone { border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
    .drop-zone:hover, .drop-zone-active { border-color: #667eea; background: #f0f4ff; }
    .drop-icon { width: 2rem; height: 2rem; margin: 0 auto 0.75rem; color: #9ca3af; }
    .drop-text { font-weight: 500; color: #374151; margin: 0; }
    .drop-hint { font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0; }

    .selected-file { margin-top: 1rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.5rem; }
    .selected-file p { margin: 0.25rem 0; font-size: 0.875rem; color: #374151; }
    .file-meta { color: #6b7280; }
    .file-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }

    .error-message { margin-top: 0.75rem; padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 0.375rem; font-size: 0.875rem; }

    .uploading-state { text-align: center; padding: 2rem 0; }
    .spinner { width: 2rem; height: 2rem; margin: 0 auto 1rem; color: #667eea; }
    .progress-bar { margin-top: 1rem; width: 100%; height: 0.5rem; background: #e5e7eb; border-radius: 9999px; overflow: hidden; }
    .progress-fill { height: 100%; background: #667eea; transition: width 0.3s ease; }

    .preview-card { display: flex; gap: 1rem; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 0.5rem; margin-bottom: 1rem; }
    .preview-thumb { width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.375rem; border: 1px solid #e5e7eb; }
    .preview-icon { width: 4rem; height: 4rem; display: flex; align-items: center; justify-content: center; background: #e5e7eb; border-radius: 0.375rem; color: #6b7280; }
    .preview-info { flex: 1; min-width: 0; }
    .preview-name { font-weight: 500; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .preview-size { font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0; }

    .modal-footer { padding: 1rem 1.5rem; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem; }
</style>
@endpush

@push('scripts')
<script>
// O script JS permanece exatamente igual ao seu original.
function uploadModal(id, context, accept, maxSize, folder, csrf) {
    return {
        id, accept, maxSize, folder, csrf,
        currentContext: context || null,
        open: false,
        step: 'select',
        dragOver: false,
        selectedFile: { name: '', size: 0, type: '', file: null },
        error: null,
        progress: 0,
        media: { alt: '', caption: '', name: '', size_formatted: '', is_image: false, thumbnail_url: '' },

        close() {
            if (this.step === 'metadata') {
                window.dispatchEvent(new CustomEvent('media:updated', {
                    detail: {
                        media: this.media,
                        source: this.currentContext || this.id
                    }
                }));
            }
            this.open = false;
            setTimeout(() => this.reset(), 200);
        },

        reset() {
            this.step = 'select';
            this.dragOver = false;
            this.selectedFile = { name: '', size: 0, type: '', file: null };
            this.error = null;
            this.progress = 0;
            this.media = { alt: '', caption: '', name: '', size_formatted: '', is_image: false, thumbnail_url: '' };
        },

        selectFile(file) {
            if (!file) return;
            if (file.size > this.maxSize * 1024) {
                this.error = 'Arquivo muito grande';
                return;
            }
            this.selectedFile = {
                name: file.name,
                size: file.size,
                type: file.type,
                file: file
            };
            this.error = null;
        },

        resetSelection() {
            this.selectedFile = { name: '', size: 0, type: '', file: null };
            this.error = null;
        },

        handleDrop(e) {
            const file = e.dataTransfer.files?.[0];
            if (file) this.selectFile(file);
        },

        confirmAndUpload() {
            if (!this.selectedFile?.file) return;
            this.upload(this.selectedFile.file);
        },

        upload(file) {
            this.step = 'uploading';
            this.error = null;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder', this.folder);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/admin/media', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrf);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) this.progress = Math.round((e.loaded / e.total) * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success && res.data) {
                            this.media = res.data;
                            this.step = 'metadata';
                            window.dispatchEvent(new CustomEvent('media:uploaded', {
                                detail: {
                                    media: res.data,
                                    source: this.currentContext || this.id
                                }
                            }));
                        } else {
                            this.error = res.message || 'Erro ao processar upload';
                            this.step = 'select';
                        }
                    } catch {
                        this.error = 'Resposta inválida do servidor';
                        this.step = 'select';
                    }
                } else {
                    this.error = 'Erro no servidor';
                    this.step = 'select';
                }
            };

            xhr.onerror = () => {
                this.error = 'Erro de conexão';
                this.step = 'select';
            };

            xhr.send(formData);
        },

        async save() {
            if (!this.media?.id) return;

            const saveBtn = document.querySelector('.modal-footer button.admin-btn-primary');
            const originalText = saveBtn?.innerText;
            if (saveBtn) saveBtn.disabled = true;

            try {
                const response = await fetch(`/admin/media/${this.media.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        alt: this.media.alt?.trim() || null,
                        caption: this.media.caption?.trim() || null,
                        name: this.media.name?.trim()
                    })
                });

                if (!response.ok) {
                    const error = await response.json().catch(() => ({ message: 'Erro ao salvar' }));
                    throw new Error(error.message || 'Falha na atualização');
                }

                const result = await response.json();

                if (result.data) {
                    this.media = { ...this.media, ...result.data };
                }

                window.dispatchEvent(new CustomEvent('media:updated', {
                    detail: {
                        media: result.data,
                        source: this.currentContext || this.id
                    }
                }));
                this.close();

            } catch(e) {
                console.error('Erro ao salvar metadados:', e);
                this.error = e.message || 'Não foi possível salvar. Tente novamente.';
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    if (originalText) saveBtn.innerText = originalText;
                }
            }
        },

        formatSize(kb) {
            if (kb >= 1024*1024) return (kb/1024/1024).toFixed(1) + ' GB';
            if (kb >= 1024) return (kb/1024).toFixed(1) + ' MB';
            return kb + ' KB';
        }
    }
}
</script>
@endpush
