@extends('admin.layout')
@section('header_title', 'Editar Banner')
@section('header_subtitle', 'Atualize as propriedades do banner: ' . $banner->title)

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/page-media.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/banners/css/banners.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-square-pen class="lucid-icon" /> Editar Banner</h2>
        <a href="{{ route('admin.banners.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.banners.update', $banner->id) }}" id="banner_form">
        @csrf
        @method('PUT')
        <div class="banner-form-inner">
            <div class="main-column">
                {{-- Titulo e Slug --}}
                <div class="admin-form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="title">Titulo do Banner *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}" required class="form-input">
                        @error('title') <small class="error">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="slug">Slug *</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $banner->slug) }}" required class="form-input">
                        @error('slug') <small class="error">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Link URL --}}
                <div class="form-group">
                    <label for="link_url">URL de Destino *</label>
                    <input type="url" name="link_url" id="link_url" value="{{ old('link_url', $banner->link_url) }}" required class="form-input">
                    <small>URL completa para onde o banner redireciona</small>
                    @error('link_url') <small class="error">{{ $message }}</small> @enderror
                </div>

                <div class="admin-form-row">
                    {{-- Selecao de Hook (unico) --}}
                    <div class="form-group" style="flex: 2">
                        <label for="hook">Ponto de Exibicao no Tema (Hook opcional)</label>
                        {!! render_hooks_select([
                            'name'        => 'hook',
                            'id'          => 'hook',
                            'selected'    => old('hook', $banner->hook),
                            'placeholder' => '-- Nenhum (Apenas shortcode ou helper) --'
                        ]) !!}
                        <small>O banner sera injetado automaticamente no hook selecionado</small>
                    </div>

                    {{-- Classes CSS --}}
                    <div class="form-group" style="flex: 1">
                        <label for="class">Classes CSS (opcional)</label>
                        <input type="text" name="class" id="class" value="{{ old('class', $banner->class) }}" class="form-input" placeholder="ex: rounded-lg shadow-lg">
                        <small>Classes aplicadas ao elemento do banner</small>
                    </div>
                </div>
            </div>

            <div class="aside-column">
                {{-- Status e Target --}}
                <div class="edit-box">
                    <header>Configuracoes</header>
                    <article>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="is_active" id="status" class="form-input">
                                <option value="1" {{ old('is_active', $banner->is_active) ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active', $banner->is_active) === false ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="target">Destino do Link</label>
                            <select name="target" id="target" class="form-input">
                                <option value="_self" {{ old('target', $banner->target) == '_self' ? 'selected' : '' }}>Mesma aba (_self)</option>
                                <option value="_blank" {{ old('target', $banner->target) == '_blank' ? 'selected' : '' }}>Nova aba (_blank)</option>
                            </select>
                        </div>
                        <div class="buttons">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <x-lucide-save class="lucid-icon" />
                                Atualizar
                            </button>
                        </div>
                    </article>
                </div>

                {{-- Imagem do Banner - usa thumbnailManager do core --}}
                <div class="edit-box" x-data="thumbnailManager({{ json_encode(['id' => $banner->image_id, 'url' => $banner->image_url]) }})">
                    <header>Imagem do Banner *</header>
                    <article>
                        <div class="form-group thumbnail">
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
                                            @click="$dispatch('media:upload-open', { id: 'bannerUploader', context: 'thumbnail' })"
                                            class="admin-btn admin-btn-secondary">
                                        <x-lucide-upload class="lucid-icon" /> Upload
                                    </button>
                                    <button type="button"
                                            @click="openSelector()"
                                            class="admin-btn admin-btn-secondary">
                                        <x-lucide-library class="lucid-icon" /> Biblioteca
                                    </button>
                                </div>

                                <input type="hidden" name="image_id" x-model="thumbnailId" required>
                                @error('image_id') <small class="error">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <div class="buttons bottom-buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Atualizar Banner
            </button>
        </div>
    </form>
</div>

{{-- Modal de selecao de midia --}}
<x-modal id="selectorModal" title="Selecionar Imagem" size="xl">
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
    id="bannerUploader"
    folder="banners"
    accept="image/*"
    :max-size="5120"
/>

@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="{{ asset('js/page-media.js') }}"></script>
@endpush
