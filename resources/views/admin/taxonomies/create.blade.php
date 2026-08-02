@extends('admin.layout')

@section('header_title', 'Nova Taxonomia')
@section('header_subtitle', 'Crie um novo tipo de classificação')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tags class="lucid-icon" /> Nova Taxonomia</h2>
        <a href="{{ route('admin.taxonomies.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.taxonomies.store') }}">
        @csrf

        <div class="admin-form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                <small>Ex: Categorias, Tags, Assuntos</small>
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                <small>URL amigável (ex: categorias, tags)</small>
                @error('slug') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea name="description" id="description" rows="3">{{ old('description') }}</textarea>
                @error('description') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label>Tipos de publicação</label>
                <div class="post-types">
                    @foreach(\App\Support\PublicationTypes::all() as $typeKey => $typeLabel)
                        <label>
                            <input type="checkbox"
                                name="target_types[]"
                                value="{{ $typeKey }}"
                                {{ isset($taxonomy) && is_array($taxonomy->target_types) && in_array($typeKey, $taxonomy->target_types) ? 'checked' : '' }}>
                            <span>{{ $typeLabel }}</span>
                        </label>
                    @endforeach
                </div>
                <small>Se nenhum for selecionado, a taxonomia estará disponível para todos os tipos.</small>
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="hierarchical">Hierárquica?</label>
                <x-switch name="hierarchical" id="hierarchical" checked="{{ old('hierarchical') ? true : false }}" active="Sim" inactive="Não" />
                <small>Termos podem ter sub-termos (ex: Categorias com subcategorias)</small>
            </div>
            <div class="form-group">
                <label for="unique">Única?</label>
                <x-switch name="unique" id="unique" checked="{{ old('unique') ? true : false }}" active="Sim" inactive="Não" />
                <small>
                    Se marcado, cada publicação poderá usar apenas um termo.
                </small>
            </div>
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .post-types {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        min-height: 5.4rem;
        padding: 0.5rem;
        border-radius: 8px;
        background-color: var(--color-bg-dark);
        border: 1px solid var(--color-border);
    }
    .post-types label {
        cursor: pointer;
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.dataset.manuallyEdited) {
            slugField.value = slugify(this.value);
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });

    function slugify(text) {
        return text.toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
</script>
@endpush
