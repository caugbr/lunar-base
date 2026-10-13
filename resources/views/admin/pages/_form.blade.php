@php
    $isEdit = $page->exists;
@endphp

<form method="POST" action="{{ $action }}" id="{{ $isEdit ? 'edit' : 'create' }}_form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="edit-page-inner">
        <div class="main-column">
            {{-- Título --}}
            <div class="admin-form-row">
                <div class="form-group">
                    <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" placeholder="Título da página" required>
                    @error('title') <small class="error">{{ $message }}</small> @enderror
                </div>
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
                    <div class="form-group excerpt">
                        <textarea name="excerpt" id="excerpt" rows="4">{{ old('excerpt', $page->excerpt) }}</textarea>
                        <small>Breve resumo da página (opcional)</small>
                    </div>
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

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select name="status" id="status" required>
                            <option value="draft" {{ old('status', $page->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ old('status', $page->status) == 'archived' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                    </div>

                    @if($isEdit)
                        <x-set-robots :model="$page" />
                    @else
                        <x-set-robots type="page" />
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
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}" required>
                        <small>URL amigável (ex: termos-de-uso, politica-privacidade)</small>
                        @error('slug') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <x-select-input
                            name="namespace"
                            label="Namespace opcional"
                            :options="$namespaces"
                            :value="old('namespace', $page->namespace)"
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
                                <option value="{{ $value }}" {{ old('template', $page->template ?? config('pageTemplates.default')) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small>Define como a página será exibida publicamente</small>
                        @error('template') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="parent_id">Página mãe</label>
                        <x-page-picker
                            name="parent_id"
                            id="parent_id"
                            :selected="old('parent_id', $page->parent_id ?? '')"
                            placeholder="-- Nenhuma --"
                            :exclude="$page->id ?? null"
                        />
                        <small>Define a página mãe dessa página (opcional)</small>
                        @error('parent_id') <small class="error">{{ $message }}</small> @enderror
                    </div>

                    <x-hook name="admin.page_properties" :params="['page' => $page ?? null]" desc="No final do box Propriedades na criação/edição de páginas" />
                </article>
            </div>

            @php
            $thumbInitial = [
                'id' => old('thumbnail_id', $page->thumbnail_id),
                'url' => old('thumbnail_url', optional($page->thumbnail)->url ?? '')
            ];
            @endphp

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
        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</form>

@if($isEdit)
    <x-lost-changes-warn selector="#edit_form" />
@endif
