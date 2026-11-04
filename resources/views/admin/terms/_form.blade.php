@php
    $term = $term ?? new \App\Models\Term();
    $isEdit = $term->exists;
    $currentTaxonomyId = old('taxonomy_id', $term->taxonomy_id ?? ($selectedTaxonomy->id ?? ''));

    $taxonomyOptions = collect($taxonomies)->pluck('name', 'id')->all();
    $hierarchicalMap = collect($taxonomies)->pluck('hierarchical', 'id')->all();

    $parentOptions = collect($parentTerms ?? [])
        ->filter(fn($parent) => !$isEdit || $parent->id !== $term->id)
        ->pluck('name', 'id')
        ->all();
@endphp

<x-form
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    id="term_form"
    :warn-changes="$isEdit"
>
    <div class="form-row">
        <x-form.select
            name="taxonomy_id"
            id="taxonomy_id"
            label="Taxonomia"
            :options="$taxonomyOptions"
            :value="$currentTaxonomyId"
            placeholder="Selecione"
            :data-hierarchical-map="json_encode($hierarchicalMap)"
            required
        />

        <x-form.text
            name="name"
            id="name"
            label="Nome"
            :value="$term->name"
            required
        />
    </div>

    <div class="form-row">
        <x-form.text
            name="slug"
            id="slug"
            label="Slug"
            :value="$term->slug"
            required
        />

        <x-form.text
            type="number"
            name="order"
            id="order"
            label="Ordem"
            :value="$term->order ?? 0"
            min="0"
            help="Menor número aparece primeiro"
        />
    </div>

    <div class="parent-group-wrapper" id="parent-group">
        <x-form.select
            name="parent_id"
            id="parent_id"
            label="Termo pai"
            :options="$parentOptions"
            :value="$term->parent_id"
            placeholder="-- Nenhum --"
            help="Apenas para taxonomias hierárquicas"
        />
    </div>

    <div class="form-group">
        <x-form.textarea
            name="description"
            id="description"
            label="Descrição"
            :value="$term->description"
            rows="3"
        />
    </div>

    <x-hook
        :name="$isEdit ? 'admin.edit_term' : 'admin.create_term'"
        :params="['term' => $term]"
        desc="No formulário de criação/edição de termo"
    />

    <div class="buttons">
        <x-form.button type="submit" variant="primary" icon="save">
            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-form.button>
    </div>
</x-form>

@push('styles')
<style>
    .parent-group-wrapper {
        display: none;
        margin-bottom: 1.25rem;
    }

    .parent-group-wrapper.is-visible {
        display: block;
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

    const taxonomySelect = document.getElementById('taxonomy_id');
    const parentGroup = document.getElementById('parent-group');
    const parentSelect = document.getElementById('parent_id');

    const checkHierarchical = () => {
        if (!taxonomySelect || !parentGroup) return;

        let map = {};
        try {
            map = JSON.parse(taxonomySelect.dataset.hierarchicalMap || '{}');
        } catch (e) {
            map = {};
        }

        const isHierarchical = Boolean(map[taxonomySelect.value]);

        parentGroup.classList.toggle('is-visible', isHierarchical);
        if (!isHierarchical && parentSelect) {
            parentSelect.value = '';
        }
    };

    if (taxonomySelect) {
        taxonomySelect.addEventListener('change', checkHierarchical);
        checkHierarchical();
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
