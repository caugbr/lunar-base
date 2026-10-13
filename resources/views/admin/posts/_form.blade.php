@php
    $post = $post ?? new \App\Models\Post();
    $isEdit = $post->exists;
@endphp

<form method="POST" action="{{ $action }}" id="{{ $isEdit ? 'edit_form' : 'create_form' }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="edit-page-inner">
        <div class="main-column">
            {{-- Título --}}
            <div class="admin-form-row">
                <div class="form-group">
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" placeholder="Título do post" required>
                    @error('title') <small class="error">{{ $message }}</small> @enderror
                </div>
            </div>

            {{-- CONTEÚDO (COMPONENTE X-EDITOR) --}}
            <div class="form-group">
                <x-editor
                    name="content"
                    json-name="content_json"
                    :value="old('content', $post->content)"
                    :json="old('content_json', $post->content_json)"
                />
                @error('content') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="edit-box">
                <header>Descrição curta</header>
                <article>
                    <div class="form-group excerpt">
                        <textarea name="excerpt" id="excerpt" rows="4">{{ old('excerpt', $post->excerpt) }}</textarea>
                        <small>Breve resumo do post (opcional — gera automaticamente do conteúdo se vazio)</small>
                    </div>
                </article>
            </div>

            {{-- Taxonomias --}}
            <x-render name="taxonomy_fields" :params="array_filter(['type' => 'post', 'item' => $isEdit ? $post : null])" />

            @if(setting('navigation.show_meta_fields'))
            <div class="edit-box">
                <header>Metadados</header>
                <article>
                    <x-meta-editor
                        name="meta"
                        :existingKeys="$existingMetaKeys"
                        :values="$postMeta ?? []"
                    />
                </article>
            </div>
            @endif

            <x-hook
                :name="$isEdit ? 'admin.edit_post_main_boxes' : 'admin.create_post_main_boxes'"
                :params="['post' => $post]"
                desc="Dentro da coluna principal na criação/edição de post"
            />
        </div>

        <div class="aside-column">
            <div class="edit-box">
                <header>Detalhes do post</header>
                <article>
                    <div class="form-group">
                        <label for="author_id">Autor</label>
                        <select name="author_id" id="author_id">
                            <option value="">-- Selecione um autor --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('author_id', $post->author_id ?? Auth::id()) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                        @error('author_id') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="published_at">Data de publicação</label>
                        <input type="datetime-local" name="published_at" id="published_at"
                            value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : ($isEdit ? '' : now()->format('Y-m-d\TH:i'))) }}">
                        <small>{{ $isEdit ? 'Altere para agendar ou reagendar' : 'Deixe em branco para publicar agora (se status = Publicado)' }}</small>
                        @error('published_at') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select name="status" id="status" required>
                            <option value="draft" {{ old('status', $post->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ old('status', $post->status) == 'archived' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                    </div>

                    @if($isEdit)
                        <x-set-robots :model="$post" />
                    @else
                        <x-set-robots type="post" />
                    @endif

                    <div class="buttons">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </article>
            </div>

            <div class="edit-box">
                <header>Propriedades</header>
                <article>
                    <div class="form-group">
                        <label for="slug">Slug *</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}" required>
                        <small>URL amigável (ex: introducao-laravel, dicas-vue-js)</small>
                        @error('slug') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="template">Template do post</label>
                        <select name="template" id="template">
                            @foreach($templates as $value => $label)
                                <option value="{{ $value }}" {{ old('template', $post->template ?? config('postTemplates.default')) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small>Define como o post será exibido publicamente</small>
                        @error('template') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>
                            <x-switch name="featured" checked="{{ old('featured', $post->featured) }}" active="" inactive="" />
                            <span>Destacar na home</span>
                        </label>
                        <small>Exibe este post em destaque na página inicial</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <x-switch name="sticky" checked="{{ old('sticky', $post->sticky) }}" active="" inactive="" />
                            <span>Fixar no topo</span>
                        </label>
                        <small>Mantém este post sempre no topo da listagem</small>
                    </div>

                    <x-hook name="admin.post_properties" :params="['post' => $post ?? null]" desc="No final do box Propriedades na criação/edição de posts" />
                </article>
            </div>

            @php
            $thumbInitial = [
                'id' => old('thumbnail_id', $post->thumbnail_id),
                'url' => old('thumbnail_url', optional($post->thumbnail)->url ?? '')
            ];
            @endphp

            <div class="edit-box" x-data='thumbnailManager({{ json_encode($thumbInitial) }})'>
                <header>Imagem do post</header>
                <article>
                    <div class="thumbnail-selector">
                        <div class="thumbnail-preview"
                             :class="{ 'has-image': thumbnailUrl }"
                             @click="!thumbnailUrl && openSelector()">
                            <template x-if="thumbnailUrl">
                                <img :src="thumbnailUrl" alt="Preview" class="preview-image" @click="openSelector()">
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
                </article>
            </div>

            <x-hook
                :name="$isEdit ? 'admin.edit_post_side_boxes' : 'admin.create_post_side_boxes'"
                :params="['post' => $post]"
                desc="Dentro da coluna lateral na criação/edição de post"
            />
        </div>
    </div>

    <div class="buttons bottom-buttons">
        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> Salvar
        </button>
    </div>
</form>

@if($isEdit)
    <x-lost-changes-warn selector="#edit_form" />
@endif
