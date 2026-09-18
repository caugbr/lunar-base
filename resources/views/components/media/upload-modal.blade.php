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
    <!-- Camada de fundo (Backdrop) -->
    <div x-show="open" x-transition.opacity.duration.250ms @click="close()" class="modal-backdrop"></div>

    <!-- Caixa principal do modal -->
    <div x-show="open" x-transition.duration.250ms @click.stop class="modal-box" :class="{ 'modal-box-expanded': step === 'focal_point' }">
        <!-- Cabeçalho -->
        <div class="modal-header">
            <h3 x-text="step === 'focal_point' ? 'Ajustar Ponto Focal' : 'Upload de Mídia'"></h3>
            <button type="button" @click="close()" class="modal-close-button">
                <x-lucide-x class="modal-icon" />
            </button>
        </div>

        <!-- Conteúdo dinâmico -->
        <div class="modal-body">

            <!-- Estado 1: Seleção de arquivo -->
            <div x-show="step === 'select'">
                <div
                    @click="$refs.fileInput.click()"
                    class="drop-zone"
                    :class="{ 'drop-zone-active': dragOver }"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="handleDrop($event); dragOver = false"
                >
                    <input type="file" x-ref="fileInput" :accept="accept" class="hidden-file-input" @change="selectFile($event.target.files[0])">
                    <x-lucide-upload class="modal-icon drop-icon" />
                    <p class="drop-title">Clique ou arraste um arquivo</p>
                    <p class="drop-hint">Máx. <span x-text="formatSize(maxSize)"></span></p>
                </div>

                <div x-show="selectedFile.name" class="selected-file-panel">
                    <p><strong>Arquivo selecionado:</strong> <span x-text="selectedFile.name"></span></p>
                    <p class="file-meta-information"><span x-text="formatSize(selectedFile.size / 1024)"></span> • <span x-text="selectedFile.type"></span></p>
                </div>

                <p x-show="error" class="error-box" x-text="error"></p>
            </div>

            <!-- Estado 2: Upload em andamento -->
            <div x-show="step === 'uploading'" class="uploading-container">
                <x-lucide-loader class="modal-icon spinner-animation" />
                <p>Enviando <span x-text="selectedFile?.name"></span>... <span x-text="progress + '%'"></span></p>
                <div class="progress-track">
                    <div class="progress-indicator" :style="{ '--upload-progress': progress + '%' }"></div>
                </div>
            </div>

            <!-- Estado 3: Metadados da imagem -->
            <div x-show="step === 'metadata'">
                <div class="media-preview-card">
                    <img x-show="isImage()" :src="media?.url" class="media-preview-thumbnail">
                    <div x-show="!isImage()" class="media-preview-placeholder"><x-lucide-file class="modal-icon" /></div>
                    <div class="media-preview-details">
                        <p class="media-filename" x-text="media?.name"></p>
                        <p class="media-filesize" x-text="media?.size_formatted || formatSize(selectedFile.size / 1024)"></p>
                    </div>
                </div>

                <!-- Campo de seleção de Posição / Corte -->
                <div x-show="isImage()" class="form-group">
                    <label class="form-label">Posição do corte</label>
                    <div class="focal-select-row">
                        <select x-model="cropMode" @change="changeCropMode($event.target.value)" class="form-input-select">
                            <option value="center">Centralizado (Padrão 50% / 50%)</option>
                            <option value="custom">Definir manualmente...</option>
                        </select>
                        <button
                            type="button"
                            x-show="cropMode === 'custom'"
                            @click="openFocalPicker()"
                            class="admin-button admin-button-secondary button-adjust-focal"
                        >
                            <x-lucide-focus class="modal-icon button-icon" />
                            <span x-text="`Editar (${focalX}%, ${focalY}%)`"></span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Texto alternativo</label>
                    <input type="text" x-model="media.alt" class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Legenda</label>
                    <textarea x-model="media.caption" class="form-input-textarea" rows="2"></textarea>
                </div>
            </div>

            <!-- Estado 4: Tela de ajuste manual do ponto focal -->
            <div x-show="step === 'focal_point'" class="focal-picker-view">
                <p class="focal-picker-instructions">Clique ou arraste a mira para o ponto principal da imagem:</p>

                <div
                    class="focal-canvas-wrapper"
                    x-ref="focalContainer"
                    @mousedown="startFocalDrag($event)"
                    @mousemove="processFocalDrag($event)"
                    @mouseup="stopFocalDrag()"
                    @mouseleave="stopFocalDrag()"
                    @touchstart="startFocalDrag($event)"
                    @touchmove="processFocalDrag($event)"
                    @touchend="stopFocalDrag()"
                >
                    <img :src="media?.url" class="focal-canvas-image" draggable="false">

                    <!-- Mira posicionada dinamicamente via variáveis CSS -->
                    <div class="focal-target-marker" :style="{ '--marker-x': tempFocalX + '%', '--marker-y': tempFocalY + '%' }">
                        <div class="focal-target-center"></div>
                    </div>
                </div>

                <div class="focal-canvas-actions">
                    <button type="button" @click="resetFocalToCenter()" class="action-link-button">Centralizar (50% / 50%)</button>
                </div>
            </div>

        </div>

        <!-- Rodapé do Modal -->
        <div class="modal-footer">
            <template x-if="step !== 'focal_point'">
                <div class="footer-button-group">
                    <button type="button" @click="resetSelection()" class="admin-button admin-button-secondary" x-show="selectedFile.name && step !== 'metadata'">Trocar</button>
                    <button type="button" @click="close()" class="admin-button admin-button-secondary">
                        <span x-text="step === 'metadata' ? 'Fechar' : 'Cancelar'"></span>
                    </button>
                    <button type="button" @click="confirmAndUpload()" class="admin-button admin-button-primary" x-show="selectedFile.name && step !== 'metadata'">Enviar</button>
                    <button type="button" x-show="step === 'metadata'" @click="save()" class="admin-button admin-button-primary">Salvar</button>
                </div>
            </template>

            <template x-if="step === 'focal_point'">
                <div class="footer-button-group">
                    <button type="button" @click="cancelFocalPicker()" class="admin-button admin-button-secondary">Cancelar</button>
                    <button type="button" @click="applyFocalPoint()" class="admin-button admin-button-primary">Confirmar Ponto</button>
                </div>
            </template>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak], .hidden-file-input { display: none !important; }

    .modal-overlay { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .modal-backdrop { position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); }
    .modal-box { position: relative; width: 100%; max-width: 28rem; background-color: #ffffff; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; transition: max-width 0.2s ease; }
    .modal-box-expanded { max-width: 44rem; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
    .modal-header h3 { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; }
    .modal-close-button { background: none; border: none; padding: 0.25rem; color: #9ca3af; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: color 0.2s ease; }
    .modal-close-button:hover { color: #374151; }
    .modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }

    .drop-zone { border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: border-color 0.2s ease, background-color 0.2s ease; }
    .drop-zone:hover, .drop-zone-active { border-color: #6366f1; background-color: #f5f7ff; }
    .drop-icon { width: 2rem; height: 2rem; margin: 0 auto 0.75rem; color: #9ca3af; }
    .drop-title { font-weight: 500; color: #374151; margin: 0; }
    .drop-hint { font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0; }

    .selected-file-panel { margin-top: 1rem; padding: 1rem; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.5rem; }
    .selected-file-panel p { margin: 0.25rem 0; font-size: 0.875rem; color: #374151; }
    .file-meta-information { color: #6b7280; }
    .error-box { margin-top: 0.75rem; padding: 0.75rem; background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 0.375rem; font-size: 0.875rem; }

    .uploading-container { text-align: center; padding: 2rem 0; }
    .spinner-animation { width: 2rem; height: 2rem; margin: 0 auto 1rem; color: #6366f1; animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .progress-track { margin-top: 1rem; width: 100%; height: 0.5rem; background-color: #e5e7eb; border-radius: 9999px; overflow: hidden; }
    .progress-indicator { height: 100%; width: var(--upload-progress, 0%); background-color: #6366f1; transition: width 0.3s ease; }

    .media-preview-card { display: flex; gap: 1rem; align-items: center; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem; margin-bottom: 1rem; }
    .media-preview-thumbnail { width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.375rem; border: 1px solid #e5e7eb; }
    .media-preview-placeholder { width: 4rem; height: 4rem; display: flex; align-items: center; justify-content: center; background-color: #e5e7eb; border-radius: 0.375rem; color: #6b7280; }
    .media-preview-details { flex: 1; min-width: 0; }
    .media-filename { font-weight: 500; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-filesize { font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0; }

    .form-group { margin-bottom: 1rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
    .form-input-text, .form-input-textarea, .form-input-select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; color: #1f2937; box-sizing: border-box; background-color: #ffffff; }

    .focal-select-row { display: flex; gap: 0.5rem; align-items: center; }
    .button-adjust-focal { white-space: nowrap; padding: 0.5rem 0.75rem; }

    .focal-picker-view { display: flex; flex-direction: column; align-items: center; }
    .focal-picker-instructions { font-size: 0.875rem; color: #4b5563; margin-bottom: 0.75rem; width: 100%; }
    .focal-canvas-wrapper { position: relative; display: inline-block; max-width: 100%; max-height: 55vh; user-select: none; cursor: crosshair; border-radius: 0.5rem; overflow: hidden; background-color: #f3f4f6; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1); }
    .focal-canvas-image { display: block; max-width: 100%; max-height: 55vh; width: auto; height: auto; pointer-events: none; }
    .focal-target-marker { position: absolute; left: var(--marker-x, 50%); top: var(--marker-y, 50%); width: 32px; height: 32px; margin-left: -16px; margin-top: -16px; border-radius: 50%; border: 2px solid #ffffff; background-color: rgba(99, 102, 241, 0.4); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3), 0 4px 12px rgba(0, 0, 0, 0.4); pointer-events: none; display: flex; align-items: center; justify-content: center; transition: transform 0.08s ease; }
    .focal-target-center { width: 6px; height: 6px; background-color: #ffffff; border-radius: 50%; box-shadow: 0 0 2px rgba(0, 0, 0, 0.8); }
    .focal-canvas-actions { margin-top: 0.75rem; width: 100%; display: flex; justify-content: flex-end; }
    .action-link-button { background: none; border: none; color: #4f46e5; font-size: 0.8125rem; cursor: pointer; text-decoration: underline; padding: 0; }

    .button-icon { margin-right: 0.5rem; }
    .modal-footer { padding: 1rem 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; }
    .footer-button-group { display: flex; justify-content: flex-end; gap: 0.75rem; width: 100%; }
    .admin-button { display: inline-flex; align-items: center; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.375rem; cursor: pointer; transition: background-color 0.2s ease, border-color 0.2s ease; border: 1px solid transparent; }
    .admin-button-primary { background-color: #6366f1; color: #ffffff; }
    .admin-button-primary:hover { background-color: #4f46e5; }
    .admin-button-secondary { background-color: #ffffff; border-color: #d1d5db; color: #374151; }
    .admin-button-secondary:hover { background-color: #f3f4f6; }
</style>
@endpush

@push('scripts')
<script>
function uploadModal(id, context, accept, maxSize, folder, csrf) {
    return {
        id,
        accept,
        maxSize,
        folder,
        csrf,
        currentContext: context || null,
        open: false,
        step: 'select',
        dragOver: false,
        selectedFile: { name: '', size: 0, type: '', file: null },
        error: null,
        progress: 0,
        media: { alt: '', caption: '', name: '', size_formatted: '', is_image: false, thumbnail_url: '', meta: {} },

        // Ponto focal e modo de corte
        cropMode: 'center',
        focalX: 50,
        focalY: 50,
        tempFocalX: 50,
        tempFocalY: 50,
        isDragging: false,

        close() {
            if (this.step === 'metadata' || this.step === 'focal_point') {
                window.dispatchEvent(new CustomEvent('media:updated', {
                    detail: { media: this.media, source: this.currentContext || this.id }
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
            this.media = { alt: '', caption: '', name: '', size_formatted: '', is_image: false, thumbnail_url: '', meta: {} };
            this.cropMode = 'center';
            this.focalX = 50;
            this.focalY = 50;
            this.tempFocalX = 50;
            this.tempFocalY = 50;
        },

        // Identifica com segurança se o arquivo carregado é imagem
        isImage() {
            if (this.media?.is_image) return true;
            const fileName = this.media?.name || this.selectedFile?.name || '';
            return /\.(jpe?g|png|webp|gif|svg)$/i.test(fileName);
        },

        selectFile(file) {
            if (!file) return;
            if (file.size > this.maxSize * 1024) {
                this.error = 'Arquivo muito grande';
                return;
            }
            this.selectedFile = { name: file.name, size: file.size, type: file.type, file: file };
            this.error = null;
        },

        resetSelection() {
            this.selectedFile = { name: '', size: 0, type: '', file: null };
            this.error = null;
        },

        handleDrop(event) {
            const file = event.dataTransfer.files?.[0];
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

            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable) {
                    this.progress = Math.round((event.loaded / event.total) * 100);
                }
            };

            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success && response.data) {
                            this.media = response.data;

                            // Se a URL não veio pronta, usa a do response ou cria objeto temporário local
                            if (!this.media.url && response.url) {
                                this.media.url = response.url;
                            } else if (!this.media.url && file) {
                                this.media.url = URL.createObjectURL(file);
                            }

                            this.focalX = this.media.meta?.focal_x ?? 50;
                            this.focalY = this.media.meta?.focal_y ?? 50;
                            this.tempFocalX = this.focalX;
                            this.tempFocalY = this.focalY;

                            this.cropMode = (this.focalX === 50 && this.focalY === 50) ? 'center' : 'custom';
                            this.step = 'metadata';

                            window.dispatchEvent(new CustomEvent('media:uploaded', {
                                detail: { media: response.data, source: this.currentContext || this.id }
                            }));
                        } else {
                            this.error = response.message || 'Erro ao processar upload';
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

        changeCropMode(mode) {
            this.cropMode = mode;
            if (mode === 'center') {
                this.focalX = 50;
                this.focalY = 50;
                this.tempFocalX = 50;
                this.tempFocalY = 50;
            } else if (mode === 'custom') {
                this.openFocalPicker();
            }
        },

        openFocalPicker() {
            this.tempFocalX = this.focalX;
            this.tempFocalY = this.focalY;
            this.step = 'focal_point';
        },

        cancelFocalPicker() {
            this.tempFocalX = this.focalX;
            this.tempFocalY = this.focalY;
            this.cropMode = (this.focalX === 50 && this.focalY === 50) ? 'center' : 'custom';
            this.step = 'metadata';
        },

        applyFocalPoint() {
            this.focalX = this.tempFocalX;
            this.focalY = this.tempFocalY;
            this.cropMode = (this.focalX === 50 && this.focalY === 50) ? 'center' : 'custom';
            this.step = 'metadata';
        },

        resetFocalToCenter() {
            this.tempFocalX = 50;
            this.tempFocalY = 50;
        },

        startFocalDrag(event) {
            this.isDragging = true;
            this.updateFocalCoordinates(event);
        },

        processFocalDrag(event) {
            if (!this.isDragging) return;
            this.updateFocalCoordinates(event);
        },

        stopFocalDrag() {
            this.isDragging = false;
        },

        updateFocalCoordinates(event) {
            const container = this.$refs.focalContainer;
            if (!container) return;

            const rect = container.getBoundingClientRect();
            const clientX = event.touches ? event.touches[0].clientX : event.clientX;
            const clientY = event.touches ? event.touches[0].clientY : event.clientY;

            let percentageX = ((clientX - rect.left) / rect.width) * 100;
            let percentageY = ((clientY - rect.top) / rect.height) * 100;

            percentageX = Math.max(0, Math.min(100, percentageX));
            percentageY = Math.max(0, Math.min(100, percentageY));

            this.tempFocalX = Math.round(percentageX);
            this.tempFocalY = Math.round(percentageY);
        },

        async save() {
            if (!this.media?.id) return;

            const saveButton = document.querySelector('.modal-footer button.admin-button-primary');
            const originalText = saveButton?.innerText;
            if (saveButton) saveButton.disabled = true;

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
                        name: this.media.name?.trim(),
                        focal_x: this.focalX,
                        focal_y: this.focalY
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
                    detail: { media: result.data, source: this.currentContext || this.id }
                }));
                this.close();

            } catch (error) {
                console.error('Erro ao salvar metadados:', error);
                this.error = error.message || 'Não foi possível salvar. Tente novamente.';
            } finally {
                if (saveButton) {
                    saveButton.disabled = false;
                    if (originalText) saveButton.innerText = originalText;
                }
            }
        },

        formatSize(kilobytes) {
            if (kilobytes >= 1024 * 1024) return (kilobytes / 1024 / 1024).toFixed(1) + ' GB';
            if (kilobytes >= 1024) return (kilobytes / 1024).toFixed(1) + ' MB';
            return kilobytes + ' KB';
        }
    };
}
</script>
@endpush
