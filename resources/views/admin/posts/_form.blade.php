@php
    $post = $post ?? new \App\Models\Post();
    $isEdit = $post->exists;

    $userOptions = collect($users)->mapWithKeys(function ($user) {
        return [$user->id => "{$user->name} ({$user->role})"];
    })->all();

    $statusOptions = [
        'draft' => 'Rascunho',
        'published' => 'Publicado',
        'archived' => 'Arquivado',
    ];

    $thumbInitial = [
        'id' => old('thumbnail_id', $post->thumbnail_id),
        'url' => old('thumbnail_url', optional($post->thumbnail)->url ?? '')
    ];
@endphp

<x-form
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    :id="$isEdit ? 'edit_form' : 'create_form'"
    :warn-changes="$isEdit"
>
    <div class="edit-page-inner">
        <div class="main-column">
            {{-- Título --}}
            <div class="form-row">
                <x-form.text
                    name="title"
                    :value="$post->title"
                    placeholder="Título do post"
                    required
                />
            </div>

            {{-- Conteúdo --}}
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
                    <x-form.textarea
                        name="excerpt"
                        rows="4"
                        :value="$post->excerpt"
                        help="Breve resumo do post (opcional — gera automaticamente do conteúdo se vazio)"
                        class="excerpt"
                    />
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
                    <x-form.select
                        name="author_id"
                        label="Autor"
                        :options="$userOptions"
                        :value="$post->author_id ?? Auth::id()"
                        placeholder="-- Selecione um autor --"
                    />

                    <x-form.date
                        type="datetime-local"
                        name="published_at"
                        label="Data de publicação"
                        :value="$post->published_at ?? ($isEdit ? null : now())"
                        :help="$isEdit ? 'Altere para agendar ou reagendar' : 'Deixe em branco para publicar agora (se status = Publicado)'"
                    />

                    <x-form.select
                        name="status"
                        label="Status"
                        :options="$statusOptions"
                        :value="$post->status ?? 'draft'"
                        :placeholder="false"
                        required
                    />

                    @if($isEdit)
                        <x-set-robots :model="$post" />
                    @else
                        <x-set-robots type="post" />
                    @endif

                    <div class="buttons">
                        <x-form.button type="submit" variant="primary" icon="save">
                            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
                        </x-form.button>
                    </div>
                </article>
            </div>

            <div class="edit-box">
                <header>Propriedades</header>
                <article>
                    <x-form.text
                        name="slug"
                        label="Slug"
                        :value="$post->slug"
                        help="URL amigável (ex: introducao-laravel, dicas-vue-js)"
                        required
                    />

                    <x-form.select
                        name="template"
                        label="Template do post"
                        :options="$templates"
                        :value="$post->template ?? config('postTemplates.default')"
                        :placeholder="false"
                        help="Define como o post será exibido publicamente"
                    />

                    <x-form.switch
                        name="featured"
                        label="Destacar na home"
                        :checked="(bool) $post->featured"
                        active=""
                        inactive=""
                        help="Exibe este post em destaque na página inicial"
                    />

                    <x-form.switch
                        name="sticky"
                        label="Fixar no topo"
                        :checked="(bool) $post->sticky"
                        active=""
                        inactive=""
                        help="Mantém este post sempre no topo da listagem"
                    />

                    <x-hook name="admin.post_properties" :params="['post' => $post ?? null]" desc="No final do box Propriedades na criação/edição de posts" />
                </article>
            </div>

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
        <x-form.button type="submit" variant="primary" icon="save">
            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-form.button>
    </div>
</x-form>
