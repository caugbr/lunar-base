@extends('admin.layout')

@section('header_title', 'Nova Página')
@section('header_subtitle', 'Crie uma nova página pública')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Nova Página</h2>
        <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.pages.store') }}" id="create_form">
        @csrf

        <div class="admin-form-row">
            <div class="form-group">
                <label for="title">Título *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required>
                @error('title') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                <small>URL amigável (ex: termos-de-uso, politica-privacidade)</small>
                @error('slug') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="author_id">Autor</label>
                <select name="author_id" id="author_id">
                    <option value="">-- Selecione um autor --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('author_id', $page->author_id ?? Auth::id()) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role->name }})
                        </option>
                    @endforeach
                </select>
                @error('author_id') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" required>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="template">Template da página</label>
                <select name="template" id="template">
                    @foreach($templates as $value => $label)
                        <option value="{{ $value }}" {{ old('template', $page->template ?? config('pageTemplates.default')) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <small>Define como a página será exibida publicamente</small>
                @error('template') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row thumb-row">

            {{-- Campo Thumbnail --}}
            <div class="form-group thumbnail">
                <label>Imagem de Destaque (Thumbnail)</label>

                <div x-data="{
                        thumbnailId: {{ old('thumbnail_id') ?: 'null' }},
                        thumbnailUrl: '{{ old('thumbnail_url') ?: '' }}',
                        galleryIds: {{ json_encode(old('gallery_ids', [])) }},

                        addToGallery(id) {
                            if (!this.galleryIds.includes(id)) {
                                this.galleryIds.push(id);
                            }
                        },

                        removeFromGallery(id) {
                            this.galleryIds = this.galleryIds.filter(gid => gid !== id);
                        },

                        setMedia(media) {
                            this.thumbnailId = media.id;
                            this.thumbnailUrl = media.thumbnail_url || media.url;
                            this.addToGallery(media.id);
                        },

                        clearMedia() {
                            this.thumbnailId = null;
                            this.thumbnailUrl = '';
                            this.removeFromGallery(this.thumbnailId);
                        }
                    }"
                    {{-- Upload direto: atualiza preview E abre o grid com contexto --}}
                    @media:uploaded.window="
                    {{-- console.log('@media:uploaded', $event.detail) --}}
                        if ($event.detail.source === 'thumbnail') {
                            setMedia($event.detail.media);
                        }
                        if ($event.detail.source === 'editor') {
                            addToGallery($event.detail.media.id);
                        }
                    "

                    @media:updated.window="
                    {{-- console.log('@media:updated', $event.detail) --}}
                        if ($event.detail.source === 'thumbnail') {
                            $dispatch('modal-open', { id: 'selectorModal', context: 'thumbnail' });
                        }
                    "

                    {{-- 2. Seleção via Grid: atualiza preview e fecha modal --}}
                    @media:inserted.window="
                        if ($event.detail.source === 'thumbnail') {
                            setMedia($event.detail.media);
                            $dispatch('modal-close', { id: 'selectorModal' });
                        }
                    "

                    class="thumbnail-selector"
                >
                    {{-- Preview --}}
                    <div class="thumbnail-preview"
                        :class="{ 'has-image': thumbnailUrl }"
                        @click="!thumbnailUrl && $dispatch('modal-open', { id: 'selectorModal', context: 'thumbnail' })"
                    >
                        <template x-if="thumbnailUrl">
                            <img :src="thumbnailUrl" alt="Preview" class="preview-image">
                        </template>
                        <template x-if="!thumbnailUrl">
                            <div class="preview-placeholder">
                                <x-lucide-image class="lucid-icon" />
                                <span>Clique para selecionar</span>
                            </div>
                        </template>

                        {{-- Botão remover (só aparece se tiver imagem) --}}
                        <button type="button"
                                x-show="thumbnailUrl"
                                @click.stop="clearMedia()"
                                class="preview-remove"
                                title="Remover imagem">
                            <x-lucide-x class="lucid-icon" />
                        </button>
                    </div>

                    {{-- Ações --}}
                    <div class="thumbnail-actions" x-show="!thumbnailUrl">
                        <button type="button"
                                @click="$dispatch('media:upload-open', { id: 'mainUploader', context: 'thumbnail' })"
                                class="admin-btn admin-btn-secondary">
                            <x-lucide-upload class="lucid-icon" /> Upload
                        </button>
                        {{-- <button type="button"
                                @click="$dispatch('modal-open', { id: 'selectorModal', context: 'thumbnail' })"
                                class="admin-btn admin-btn-secondary">
                            <x-lucide-library class="lucid-icon" /> Biblioteca
                        </button> --}}
                    </div>

                    {{-- Input hidden para submissão --}}
                    <input type="hidden" name="thumbnail_id" x-model="thumbnailId">
                    <!-- Para a galeria, use um input hidden que aceita array -->
                    <template x-for="id in galleryIds">
                        <input type="hidden" name="gallery_ids[]" :value="id">
                    </template>
                </div>
            </div>

            <div class="form-group excerpt">
                <label for="excerpt">Resumo / Descrição curta</label>
                <textarea name="excerpt" id="excerpt" rows="4">{{ old('excerpt') }}</textarea>
                <small>Breve resumo da página (opcional)</small>
            </div>

        </div>

        <div class="form-group">
            <div class="editor-top">
                <label for="content">Conteúdo *</label>
                <div class="image-buttons" x-data>
                    <button class="admin-btn admin-btn-secondary" type="button" @click="$dispatch('media:upload-open',{ id: 'mainUploader', context: 'editor' })">
                        <x-lucide-upload class="lucid-icon" />
                        Upload de imagem
                    </button>
                    <button class="admin-btn admin-btn-secondary" type="button" @click="$dispatch('modal-open', { id: 'selectorModal', context: 'editor' })">
                        <x-lucide-image class="lucid-icon" />
                        Inserir imagem
                    </button>
                </div>
            </div>
            <textarea name="content" id="content" rows="15" style="display: none;"></textarea>
            <div id="tiny-editor" class="tiny-editor"></div>
            @error('content') <small class="error">{{ $message }}</small> @enderror
        </div>

        {{-- Taxonomias (Termos) --}}
        <div class="form-group">
            <label>Classificação</label>

            @foreach($taxonomies as $taxonomy)
                <div class="taxonomy-group" style="margin-bottom: 1rem; padding: 0.75rem; background: #f9fafb; border-radius: 0.5rem;">
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: 600;">
                        {{ $taxonomy->name }}
                        @if($taxonomy->description)
                            <small style="font-weight: normal; color: #6b7280;">({{ $taxonomy->description }})</small>
                        @endif
                    </h4>

                    <div class="terms-checkbox-group" style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        @foreach($taxonomy->terms as $term)
                            <label style="display: flex; align-items: center; gap: 0.25rem;">
                                <input type="checkbox"
                                    name="term_ids[]"
                                    value="{{ $term->id }}"
                                    {{ isset($selectedTermIds) && in_array($term->id, $selectedTermIds) ? 'checked' : '' }}>
                                <span>{{ $term->name }}</span>
                                @if($term->parent)
                                    <small style="color: #9ca3af;">({{ $term->parent->name }})</small>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <small>Selecione os termos que classificam esta página</small>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> Salvar
        </button>
    </form>
</div>

{{-- Modal + Grid --}}
<x-modal id="selectorModal" title="Selecionar Mídia" size="xl">
    <x-media.grid
        id="gridInsideModal"
        :selectable="true"
        :multiple="false"
        :per-page="12"
        initial-type="image"
    />
</x-modal>

{{-- Upload --}}
<x-media.upload-modal
    id="mainUploader"
    folder="uploads"
    accept="image/*,application/pdf"
    :max-size="10240"
/>

@endsection


@push('styles')
<style>
    .editor-top {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        /* align-items: baseline; */
    }
    .editor-top button {
        border-radius: 8px 8px 0 0;
    }
    .tiny-editor {
        min-height: 400px;
    }

    .thumb-row {
        display: flex;
    }

    .thumb-row .thumbnail {
        flex-shrink: 3;
        min-width: 370px;
    }

    .thumb-row .excerpt {
        flex-grow: 4;
    }

    @media (max-width: 1120px) {
        .thumb-row {
            flex-direction: column;
        }

        .thumb-row .thumbnail {
            min-width: 270px;
        }
    }

    .thumbnail-selector {
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
        align-items: flex-end;
    }
    .thumbnail-preview {
        width: 100%; max-width: 240px; aspect-ratio: 16/9;
        border: 2px dashed #d1d5db; border-radius: 0.5rem;
        background: #f9fafb; display: flex; align-items: center; justify-content: center;
        position: relative; cursor: pointer; transition: all 0.2s; overflow: hidden;
    }
    .thumbnail-preview.has-image { border-style: solid; border-color: #e5e7eb; cursor: default; }
    .thumbnail-preview:hover { border-color: #667eea; background: #f0f4ff; }
    .preview-image { width: 100%; height: 100%; object-fit: cover; }
    .preview-placeholder {
        text-align: center; color: #6b7280; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    }
    .preview-placeholder .lucid-icon { width: 2rem; height: 2rem; }
    .preview-remove {
        position: absolute; top: 0.5rem; right: 0.5rem;
        background: #ef4444; color: white; border: none; border-radius: 9999px;
        padding: 0.25rem; cursor: pointer; transition: background 0.2s;
    }
    .preview-remove:hover { background: #dc2626; }
    .thumbnail-actions { display: flex; gap: 0.5rem; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#tiny-editor',
        height: 500,
        menubar: true,
        language: 'pt_BR',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | link image | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
        // Remove tags vazias e limpa o HTML
        remove_trailing_brs: true,
        remove_linebreaks: false,
        apply_source_formatting: true,
        cleanup_on_startup: true,
        // Configuração para não criar <p><br></p> desnecessários
        forced_root_block: 'p',
        // Setup para carregar conteúdo existente e salvar no textarea
        setup: function(editor) {
            // Carrega o conteúdo existente
            var existingContent = document.getElementById('content').value;
            if (existingContent) {
                editor.on('init', function() {
                    editor.setContent(existingContent);
                });
            }

            // Salva no textarea antes de submeter
            var form = document.querySelector('#create_form');
            form.addEventListener('submit', function() {
                document.getElementById('content').value = editor.getContent();
            });
        }
    });

    // No campo título, aplicar ao digitar
    document.getElementById('title').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.dataset.manuallyEdited) {
            slugField.value = slugify(this.value);
        }
    });

    // Permite que o usuário edite manualmente o slug sem ser sobrescrito
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
});

window.addEventListener('media:inserted', (e) => {
    // Se for para o thumbnail, IGNORA aqui (deixa o Alpine cuidar)
    if (e.detail.source === 'thumbnail') return;

    const url = e.detail.media.url;
    const alt = e.detail.media.alt || '';

    tinymce.activeEditor.insertContent(`<img src="${url}" alt="${alt}">`);

    window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'selectorModal' } }));
});

window.addEventListener('media:updated', (e) => {
    window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: 'selectorModal', context: 'editor' } }));
});

function slugify(text) {
    return text
        .toString()
        .normalize('NFD')                          // Separa acentos dos caracteres
        .replace(/[\u0300-\u036f]/g, '')           // Remove acentos
        .toLowerCase()                              // Minúsculas
        .trim()                                     // Remove espaços do início/fim
        .replace(/[^a-z0-9\s-]/g, '')              // Remove caracteres especiais
        .replace(/[\s_-]+/g, '-')                  // Substitui espaços/underscores por hífen
        .replace(/^-+|-+$/g, '');                  // Remove hífens do início/fim
}
</script>
@endpush
