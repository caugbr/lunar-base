@php
    $taxonomy = $taxonomy ?? new \App\Models\Taxonomy();
    $isEdit = $taxonomy->exists;
    $selectedTypes = old('target_types', $taxonomy->target_types ?? []);
@endphp

<form method="POST" action="{{ $action }}" id="tax_form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="admin-form-row">
        <div class="form-group">
            <label for="name">Nome *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $taxonomy->name) }}" required>
            <small>Ex: Categorias, Tags, Assuntos</small>
            @error('name') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $taxonomy->slug) }}" required>
            <small>URL amigável (ex: categorias, tags)</small>
            @error('slug') <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="admin-form-row">
        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3">{{ old('description', $taxonomy->description) }}</textarea>
            @error('description') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Tipos de publicação</label>
            <div class="post-types">
                @foreach(\App\Support\PublicationTypes::all() as $typeKey => $type)
                    <label>
                        <input type="checkbox"
                            name="target_types[]"
                            value="{{ $typeKey }}"
                            {{ is_array($selectedTypes) && in_array($typeKey, $selectedTypes) ? 'checked' : '' }}>
                        <span>{{ $type['label'] }}</span>
                    </label>
                @endforeach
            </div>
            <small>Se nenhum for selecionado, a taxonomia estará disponível para todos os tipos.</small>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="form-group">
            <label for="hierarchical">Hierárquica?</label>
            <x-switch
                name="hierarchical"
                id="hierarchical"
                :checked="old('hierarchical', $taxonomy->hierarchical) ? true : false"
                active="Sim"
                inactive="Não"
            />
            <small>Termos podem ter sub-termos (ex: Categorias com subcategorias)</small>
        </div>
        <div class="form-group">
            <label for="unique">Única?</label>
            <x-switch
                name="unique"
                id="unique"
                :checked="old('unique', $taxonomy->unique) ? true : false"
                active="Única"
                inactive="Múltipla"
            />
            <small>Se marcado, cada publicação poderá usar apenas um termo.</small>
        </div>
    </div>

    <div class="buttons">
        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</form>

@if($isEdit)
    <x-lost-changes-warn selector="#tax_form" />
@endif

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
    document.addEventListener('DOMContentLoaded', function() {
        const nameField = document.getElementById('name');
        const slugField = document.getElementById('slug');

        if (nameField && slugField) {
            nameField.addEventListener('input', function() {
                if (!slugField.dataset.manuallyEdited) {
                    slugField.value = slugify(this.value);
                }
            });

            slugField.addEventListener('input', function() {
                this.dataset.manuallyEdited = 'true';
            });
        }

        function slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
</script>
@endpush
