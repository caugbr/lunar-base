@props([
    'name',
    'value' => null,
    'url' => null,
    'label' => null,
    'context' => null,
    'uploaderId' => 'mainUploader',
    'required' => false,
    'help' => 'Selecione uma imagem da biblioteca ou envie um novo arquivo.',
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);
    $resolvedContext = $context ?? $name;

    $initialId = old($dotName, $value);
    $initialUrl = old($dotName . '_url', $url ?? (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) ? $value : ''));
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </label>
    @endif

    <div class="thumbnail-selector @if($hasError) is-invalid @endif"
         x-data="imagePickerManager({
             id: @js($initialId),
             url: @js($initialUrl),
             context: @js($resolvedContext)
         })"
         @media:inserted.window="handleMediaInserted($event.detail)"
    >
        <div class="thumbnail-preview"
             :class="{ 'has-image': mediaUrl }"
             @click="!mediaUrl && openSelector()">

            <template x-if="mediaUrl">
                <img :src="mediaUrl" alt="Pré-visualização" class="preview-image" @click="openSelector()">
            </template>

            <template x-if="!mediaUrl">
                <div class="preview-placeholder">
                    <x-lucide-image class="lucid-icon" />
                    <span>Clique para selecionar</span>
                </div>
            </template>

            <button type="button"
                    x-show="mediaUrl"
                    @click.stop="clearMedia()"
                    class="preview-remove"
                    title="Remover imagem">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

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

        <input type="hidden" name="{{ $name }}" :value="mediaId" id="{{ $id }}">
    </div>

    @if($help)
        <small class="form-help">{{ $help }}</small>
    @endif

    @error($dotName)
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>

@once
<x-media.grid-modal />
<x-media.upload-modal id="{{ $uploaderId }}" folder="uploads" accept="image/*,application/pdf" :max-size="10240" />

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/picker/icon.css') }}">
@endpush

@push('scripts')
<script>
function imagePickerManager(config = {}) {
    return {
        mediaId: config.id || '',
        mediaUrl: config.url || '',
        context: config.context || 'thumbnail',

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

        handleMediaInserted(detail) {
            if (detail.source === this.context) {
                this.mediaId = detail.media.id;
                this.mediaUrl = detail.media.thumbnail_url || detail.media.url;

                window.dispatchEvent(new CustomEvent('modal-close', {
                    detail: { id: 'selectorModal' }
                }));
            }
        },

        clearMedia() {
            this.mediaId = '';
            this.mediaUrl = '';
        }
    };
}

window.addEventListener('media:uploaded', (e) => {
    window.dispatchEvent(new CustomEvent('modal-close', {
        detail: { id: 'mainUploader' }
    }));

    window.dispatchEvent(new CustomEvent('media:inserted', {
        detail: {
            media: e.detail.media,
            source: e.detail.source
        }
    }));
});
</script>
@endpush
@endonce
