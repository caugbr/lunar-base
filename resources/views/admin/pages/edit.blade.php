@extends('admin.layout')

@section('header_title', 'Editar Página')
@section('header_subtitle', 'Modifique o conteúdo da página')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Editar: {{ $page->title }}</h2>
        <div class="top-buttons">
            <a href="{{ $page->url }}" class="admin-btn admin-btn-secondary" target="_blank">
                <x-lucide-external-link class="lucid-icon" /> <span>Visitar</span>
            </a>
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $page->id) }}" id="edit_form">
        @csrf
        @method('PUT')

        {{-- Título --}}
        <div class="admin-form-row">
            <div class="form-group">
                <label for="title">Título *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required>
                @error('title') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Slug + Autor --}}
        <div class="admin-form-row">
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}" required>
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
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
                @error('author_id') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Widget + is_main + Status + Template --}}
        <div class="admin-form-row">
            <div class="form-group">
                <label for="namespace">Namespace opcional</label>
                <select name="namespace" id="namespace">
                    <option value="">-- Nenhum (página geral) --</option>
                    @foreach($namespaces as $namespace)
                        <option value="{{ $namespace }}" {{ old('namespace') == $namespace ? 'selected' : '' }}>
                            {{ $namespace }}
                        </option>
                    @endforeach
                    <option value="insert-namespace">Inserir novo...</option>
                </select>
                <input type="text" id="new_namespace">
                <small>Selecionar apenas se a página for específica de um namespace</small>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" required>
                    <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="archived" {{ old('status', $page->status) == 'archived' ? 'selected' : '' }}>Arquivado</option>
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

        @php
        $thumbInitial = [
            'id' => old('thumbnail_id', $page->thumbnail_id),
            'url' => old('thumbnail_url', optional($page->thumbnail)->url ?? '')
        ];
        @endphp

        <div class="admin-form-row thumb-row" x-data='thumbnailManager({{ json_encode($thumbInitial) }})'>
            <div class="form-group thumbnail">
                <label>Imagem de Destaque (Thumbnail)</label>

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

            <div class="form-group excerpt">
                <label for="excerpt">Resumo / Descrição curta</label>
                <textarea name="excerpt" id="excerpt" rows="4">{{ old('excerpt', $page->excerpt) }}</textarea>
                <small>Breve resumo da página (opcional)</small>
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
            <textarea name="content" id="content" rows="15" style="display: none;">{{ old('content', $page->content) }}</textarea>
            <div id="tiny-editor" class="tiny-editor"></div>
            @error('content') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Atualizar
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
<style>
    #new_namespace {
        display: none;
    }
    [data-insert-namespace] #new_namespace {
        display: block;
    }
    [data-insert-namespace] #namespace {
        display: none;
    }
</style>
@endpush

@push('scripts')
{{-- Alpine CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/page-media.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nspace = document.getElementById('namespace');
        const newnspace = document.getElementById('new_namespace');

        const undo = () => {
            const inserting = document.querySelector('.form-group[data-insert-namespace]');
            if (inserting) {
                inserting.removeAttribute('data-insert-namespace');
                if (newnspace.value) {
                    nspace.selectedIndex = nspace.options.length - 1;
                } else {
                    nspace.selectedIndex = 0;
                }
                nspace.focus();
                newnspace.value = '';
            }
        }

        if (nspace) {
            nspace.addEventListener('change', function(event) {
                if (this.value == 'insert-namespace') {
                    this.closest('.form-group').dataset.insertNamespace = true;
                    setTimeout(() => newnspace.focus(), 80);
                } else {
                    this.closest('.form-group').removeAttribute('data-insert-namespace');
                }
            });
            newnspace.addEventListener('keydown', function(event) {
                if (event.key == 'Enter') {
                    event.preventDefault();
                    if (newnspace.value) {
                        nspace.innerHTML += `<option value"${newnspace.value}">${newnspace.value}</option>`;
                    } else {
                        newnspace.value = '';
                    }
                    undo();
                }
                if (event.key == 'Escape') {
                    event.preventDefault();
                    newnspace.value = '';
                    undo();
                }
            });
        }

        document.addEventListener('click', undo);
        document.addEventListener('focus', undo);
    });
</script>
@endpush
