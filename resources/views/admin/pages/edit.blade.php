@extends('admin.layout')

@section('header_title', 'Editar Página')
@section('header_subtitle', 'Modifique o conteúdo da página')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Editar: {{ $page->title }}</h2>
        <div class="top-buttons">
            <a href="{{ $page->public_url }}" class="admin-btn admin-btn-secondary" target="_blank">
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

        <div class="admin-form-row">
            <div class="form-group">
                <label for="title">Título *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required>
                @error('title') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

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

        <div class="admin-form-row">
            <div class="form-group">
                <label for="excerpt">Resumo / Descrição curta</label>
                <textarea name="excerpt" id="excerpt" rows="2">{{ old('excerpt', $page->excerpt) }}</textarea>
                <small>Breve resumo da página (opcional)</small>
            </div>
        </div>

        <div class="form-group">
            <label for="content">Conteúdo *</label>
            <textarea name="content" id="content" rows="15" style="display: none;">{{ old('content', $page->content) }}</textarea>
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
            <x-lucide-save class="lucid-icon" /> Atualizar
        </button>
    </form>
</div>
@endsection

@push('styles')
<style>
    .tiny-editor {
        min-height: 400px;
    }
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
                var form = document.querySelector('#edit_form');
                form.addEventListener('submit', function() {
                    document.getElementById('content').value = editor.getContent();
                });
            }
        });
    });
</script>
@endpush
