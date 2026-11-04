@php
    $taxonomy = $taxonomy ?? new \App\Models\Taxonomy();
    $isEdit = $taxonomy->exists;
@endphp

<x-form
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    id="tax_form"
    :warn-changes="$isEdit"
>
    <div class="form-row">
        <x-form.text
            name="name"
            id="name"
            label="Nome"
            :value="$taxonomy->name"
            help="Ex: Categorias, Tags, Assuntos"
            required
        />

        <x-form.text
            name="slug"
            id="slug"
            label="Slug"
            :value="$taxonomy->slug"
            help="URL amigável (ex: categorias, tags)"
            required
        />
    </div>

    <div class="form-row">
        <x-form.textarea
            name="description"
            id="description"
            label="Descrição"
            :value="$taxonomy->description"
            rows="3"
        />

        <div class="post-types-panel">
            <x-picker.pubtypes
                name="target_types"
                label="Tipos de publicação"
                :value="$taxonomy->target_types ?? []"
                help="Se nenhum for selecionado, a taxonomia estará disponível para todos os tipos."
                inline
            />
        </div>
    </div>

    <div class="form-row">
        <x-form.switch
            name="hierarchical"
            id="hierarchical"
            label="Hierárquica?"
            :checked="(bool) $taxonomy->hierarchical"
            active="Sim"
            inactive="Não"
            help="Termos podem ter sub-termos (ex: Categorias com subcategorias)"
        />

        <x-form.switch
            name="unique"
            id="unique"
            label="Única?"
            :checked="(bool) $taxonomy->unique"
            active="Única"
            inactive="Múltipla"
            help="Se marcado, cada publicação poderá usar apenas um termo."
        />
    </div>

    <div class="buttons">
        <x-form.button type="submit" variant="primary" icon="save">
            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-form.button>
    </div>
</x-form>

@push('styles')
<style>
    .post-types-panel {
        flex: 1 1 0;
        min-width: 0;
    }

    .post-types-panel .checkbox-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        min-height: 5.4rem;
        padding: 0.5rem;
        border-radius: 8px;
        background-color: var(--color-bg-dark, #f8f9fa);
        border: 1px solid var(--color-border, #ced4da);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameField = document.getElementById('name');
    const slugField = document.getElementById('slug');

    if (nameField && slugField) {
        if (slugField.value.trim().length > 0) {
            slugField.dataset.manuallyEdited = 'true';
        }

        nameField.addEventListener('input', () => {
            if (!slugField.dataset.manuallyEdited) {
                slugField.value = slugify(nameField.value);
            }
        });

        slugField.addEventListener('input', () => {
            slugField.dataset.manuallyEdited = 'true';
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
