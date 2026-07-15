@extends('admin.layout')
@section('header_title', 'Novo Banner')
@section('header_subtitle', 'Crie um banner com imagem, link e hook')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/page-media.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/banners/css/banners.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-square-plus class="lucid-icon" /> Novo Banner</h2>
        <a href="{{ route('admin.banners.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.banners.store') }}" id="banner_form">
        @csrf
        <div class="banner-form-inner">
            <div class="main-column">
                {{-- Titulo e Slug --}}
                <div class="admin-form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="title">Titulo do Banner *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required class="form-input" placeholder="Ex: Promocao de Verao">
                        @error('title') <small class="error">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="slug">Slug *</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required class="form-input" placeholder="promocao-verao">
                        @error('slug') <small class="error">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Link URL --}}
                <div class="form-group">
                    <label for="link_url">URL de Destino *</label>
                    <input type="url" name="link_url" id="link_url" value="{{ old('link_url') }}" required class="form-input" placeholder="https://exemplo.com/oferta">
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
                            'selected'    => old('hook'),
                            'placeholder' => '-- Nenhum (Apenas shortcode ou helper) --'
                        ]) !!}
                        <small>O banner sera injetado automaticamente no hook selecionado</small>
                    </div>

                    {{-- Classes CSS --}}
                    <div class="form-group" style="flex: 1">
                        <label for="class">Classes CSS (opcional)</label>
                        <input type="text" name="class" id="class" value="{{ old('class') }}" class="form-input" placeholder="ex: rounded-lg shadow-lg">
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
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="target">Destino do Link</label>
                            <select name="target" id="target" class="form-input">
                                <option value="_self" {{ old('target', '_self') == '_self' ? 'selected' : '' }}>Mesma aba (_self)</option>
                                <option value="_blank" {{ old('target') == '_blank' ? 'selected' : '' }}>Nova aba (_blank)</option>
                            </select>
                        </div>
                        <div class="buttons">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <x-lucide-save class="lucid-icon" />
                                Salvar
                            </button>
                        </div>
                    </article>
                </div>

                {{-- Imagem do Banner - usa thumbnailManager do core --}}
                <div class="edit-box" x-data="thumbnailManager({{ json_encode(['id' => old('image_id'), 'url' => old('image_url')]) }})">
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
                <x-lucide-save class="lucid-icon" /> Criar Banner
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
<script>
    // Gerador de slug automatico
    document.getElementById('title').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.dataset.manuallyEdited) {
            slugField.value = this.value.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
</script>
@endpush
