@php
    $isEdit = $page->exists;

    $userOptions = collect($users)->mapWithKeys(function ($user) {
        return [$user->id => "{$user->name} ({$user->role})"];
    })->all();

    $statusOptions = [
        'draft' => 'Rascunho',
        'published' => 'Publicado',
        'archived' => 'Arquivado',
    ];

    $thumbInitial = [
        'id' => old('thumbnail_id', $page->thumbnail_id),
        'url' => old('thumbnail_url', optional($page->thumbnail)->url ?? '')
    ];
@endphp

<x-form
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    :id="($isEdit ? 'edit' : 'create') . '_form'"
    :warn-changes="$isEdit"
>
    <div class="edit-page-inner">
        <div class="main-column">
            {{-- Título --}}
            <div class="form-row">
                <x-form.text
                    name="title"
                    :value="$page->title"
                    placeholder="Título da página"
                    required
                />
            </div>

            {{-- Conteúdo --}}
            <div class="form-group">
                <x-editor
                    name="content"
                    json-name="content_json"
                    :value="old('content', $page->content)"
                    :json="old('content_json', $page->content_json)"
                />
                @error('content') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="edit-box">
                <header>Descrição curta</header>
                <article>
                    <x-form.textarea
                        name="excerpt"
                        rows="4"
                        :value="$page->excerpt"
                        help="Breve resumo da página (opcional)"
                        class="excerpt"
                    />
                </article>
            </div>

            {{-- Taxonomias --}}
            <x-render name="taxonomy_fields" :params="array_filter(['type' => 'page', 'item' => $isEdit ? $page : null])" />

            @if(setting('navigation.show_meta_fields'))
            <div class="edit-box">
                <header>Metadados</header>
                <article>
                    <x-meta-editor
                        name="meta"
                        :existingKeys="$existingMetaKeys"
                        :values="$page->meta ?? []"
                    />
                </article>
            </div>
            @endif

            <x-hook
                :name="$isEdit ? 'admin.edit_page_main_boxes' : 'admin.create_page_main_boxes'"
                :params="['page' => $page]"
                desc="Dentro da coluna principal na criação/edição de página"
            />
        </div>

        <div class="aside-column">
            <div class="edit-box">
                <header>Detalhes da página</header>
                <article>
                    <x-form.select
                        name="author_id"
                        label="Autor"
                        :options="$userOptions"
                        :value="$page->author_id ?? Auth::id()"
                        placeholder="-- Selecione um autor --"
                    />

                    <x-form.select
                        name="status"
                        label="Status"
                        :options="$statusOptions"
                        :value="$page->status ?? 'draft'"
                        :placeholder="false"
                        required
                    />

                    @if($isEdit)
                        <x-set-robots :model="$page" />
                    @else
                        <x-set-robots type="page" />
                    @endif

                    <div class="buttons">
                        <x-form.button type="submit" variant="primary">
                            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
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
                        :value="$page->slug"
                        help="URL amigável (ex: termos-de-uso, politica-privacidade)"
                        required
                    />

                    <x-form.select
                        name="namespace"
                        label="Namespace opcional"
                        :options="$namespaces"
                        :value="$page->namespace"
                        placeholder="-- Nenhum (página geral) --"
                        allow-insert
                        insert-label="Inserir novo..."
                        insert-placeholder="Digite o novo namespace"
                        help="Defina um namespace para esta página"
                    />

                    <x-form.select
                        name="template"
                        label="Template da página"
                        :options="$templates"
                        :value="$page->template ?? config('pageTemplates.default')"
                        :placeholder="false"
                        help="Define como a página será exibida publicamente"
                    />

                    <x-picker.page
                        name="parent_id"
                        label="Página mãe"
                        :value="$page->parent_id ?? ''"
                        placeholder="-- Nenhuma --"
                        :exclude="$page->id ?? null"
                        help="Define a página mãe dessa página (opcional)"
                    />

                    <x-hook name="admin.page_properties" :params="['page' => $page ?? null]" desc="No final do box Propriedades na criação/edição de páginas" />
                </article>
            </div>

            <div class="edit-box" x-data='thumbnailManager({{ json_encode($thumbInitial) }})'>
                <header>Imagem da página</header>
                <article>
                    <div class="form-group thumbnail">
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
                    </div>
                </article>
            </div>

            <x-hook
                :name="$isEdit ? 'admin.edit_page_side_boxes' : 'admin.create_page_side_boxes'"
                :params="['page' => $page]"
                desc="Dentro da coluna lateral na criação/edição de página"
            />
        </div>
    </div>

    <div class="buttons bottom-buttons">
        <x-form.button type="submit" variant="primary">
            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-form.button>
    </div>
</x-form>
