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
        <div class="edit-page-inner">
            <div class="main-column">
                {{-- Título --}}
                <div class="admin-form-row">
                    <div class="form-group">
                        <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Título da página" required>
                        @error('title') <small class="error">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Conteúdo (TinyMCE) --}}
                <div class="form-group">
                    <div class="editor-top">
                        <label for="content">Conteúdo *</label>
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
                    <textarea name="content" id="content" rows="15" style="display: none;">{{ old('content') }}</textarea>
                    <div id="tiny-editor" class="tiny-editor"></div>
                    @error('content') <small class="error">{{ $message }}</small> @enderror
                </div>
                <div class="edit-box">
                    <header>Descrição curta</header>
                    <article>
                        <div class="form-group excerpt">
                            {{-- <label for="excerpt">Resumo</label> --}}
                            <textarea name="excerpt" id="excerpt" rows="4">{{ old('excerpt') }}</textarea>
                            <small>Breve resumo da página (opcional)</small>
                        </div>
                    </article>
                </div>

                {{-- Taxonomias --}}
                @if(isset($taxonomies) && count($taxonomies))
                <div class="edit-box">
                    <header>Taxonomias</header>
                    <article>
                        <div class="form-group">
                            {{-- <label>Classificação</label> --}}
                            @foreach($taxonomies as $taxonomy)
                                <div class="taxonomy-group">
                                    <h4>
                                        {{ $taxonomy->name }}
                                        @if($taxonomy->description)
                                            <small>({{ $taxonomy->description }})</small>
                                        @endif
                                    </h4>
                                    <div class="terms-checkbox-group">
                                        @foreach($taxonomy->terms as $term)
                                            <label>
                                                <input type="checkbox" name="term_ids[]" value="{{ $term->id }}"
                                                    {{ isset($selectedTermIds) && in_array($term->id, $selectedTermIds) ? 'checked' : '' }}>
                                                <span>{{ $term->name }}</span>
                                                @if($term->parent)
                                                    <small>({{ $term->parent->name }})</small>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <small>Selecione os termos que classificam esta página</small>
                        </div>
                    </article>
                </div>
                @endif

            </div>
            <div class="aside-column">
                <div class="edit-box">
                    <header>Detalhes da página</header>
                    <article>
                        <div class="form-group">
                            <label for="author_id">Autor</label>
                            <select name="author_id" id="author_id">
                                <option value="">-- Selecione um autor --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('author_id', Auth::id()) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('author_id') <small class="error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select name="status" id="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                            </select>
                        </div>

                        <div class="buttons">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <x-lucide-save class="lucid-icon" /> Salvar
                            </button>
                        </div>
                    </article>
                </div>
                <div class="edit-box">
                    <header>Propriedades</header>
                    <article>
                        <div class="form-group">
                            <label for="slug">Slug *</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                            <small>URL amigável (ex: termos-de-uso, politica-privacidade)</small>
                            @error('slug') <small class="error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <x-select-input
                                name="namespace"
                                label="Namespace opcional"
                                :options="$namespaces"
                                :value="old('namespace')"
                                placeholder="-- Nenhum (página geral) --"
                                :allowInsert="true"
                                insertLabel="Inserir novo..."
                                insertPlaceholder="Digite o novo namespace"
                                help="Defina um namespace para esta página"
                            />
                        </div>

                        <div class="form-group">
                            <label for="template">Template da página</label>
                            <select name="template" id="template">
                                @foreach($templates as $value => $label)
                                    <option value="{{ $value }}" {{ old('template', config('pageTemplates.default')) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small>Define como a página será exibida publicamente</small>
                            @error('template') <small class="error">{{ $message }}</small> @enderror
                        </div>
                    </article>
                </div>
                <div class="edit-box">
                    <header>Imagem da página</header>
                    <article x-data='thumbnailManager(thumbnailManager(@json(["id" => old("thumbnail_id"), "url" => old("thumbnail_url")])))'>
                        <div class="form-group thumbnail">
                            {{-- <label>Imagem de Destaque (Thumbnail)</label> --}}

                            <div class="thumbnail-selector">
                                <div class="thumbnail-preview"
                                    :class="{ 'has-image': thumbnailUrl }"
                                    @click="!thumbnailUrl && openSelector()">
                                    <template x-if="thumbnailUrl">
                                        <img :src="thumbnailUrl" alt="Preview" class="preview-image">
                                    </template>
                                    <template x-if="!thumbnailUrl">
                                        <div class="preview-placeholder">
                                            <x-lucide-image class="lucid-icon" />
                                            <span>Clique para selecionar</span>
                                        </div>
                                    </template>

                                    <button type="button"
                                            x-show="thumbnailUrl"
                                            @click.stop="clearMedia()"
                                            class="preview-remove"
                                            title="Remover imagem">
                                        <x-lucide-x class="lucid-icon" />
                                    </button>
                                </div>

                                <div class="thumbnail-actions" x-show="!thumbnailUrl">
                                    <button type="button"
                                            @click="$dispatch('media:upload-open', { id: 'mainUploader', context: 'thumbnail' })"
                                            class="admin-btn admin-btn-secondary">
                                        <x-lucide-upload class="lucid-icon" /> Upload
                                    </button>
                                    <button type="button"
                                            @click="openSelector()"
                                            class="admin-btn admin-btn-secondary">
                                        <x-lucide-library class="lucid-icon" /> Biblioteca
                                    </button>
                                </div>

                                <input type="hidden" name="thumbnail_id" x-model="thumbnailId">
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <div class="buttons bottom-buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar
            </button>
        </div>
    </form>
</div>

{{-- Modal de seleção --}}
<x-modal id="selectorModal" title="Selecionar Mídia" size="xl">
    <x-media.grid
        id="gridInsideModal"
        :selectable="true"
        :multiple="false"
        :per-page="12"
        initial-type="image"
    />
</x-modal>

{{-- Upload Modal --}}
<x-media.upload-modal
    id="mainUploader"
    folder="uploads"
    accept="image/*,application/pdf"
    :max-size="10240"
/>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/page-media.css') }}">
@endpush

@push('scripts')
{{-- Alpine CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/page-media.js') }}"></script>
@endpush
