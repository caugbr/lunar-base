@php
    $term = $term ?? new \App\Models\Term();
    $isEdit = $term->exists;
    $currentTaxonomyId = old('taxonomy_id', $term->taxonomy_id ?? ($selectedTaxonomy->id ?? ''));
@endphp

<form method="POST" action="{{ $action }}" id="term_form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="admin-form-row">
        <div class="form-group">
            <label for="taxonomy_id">Taxonomia *</label>
            <select name="taxonomy_id" id="taxonomy_id" required>
                <option value="">Selecione</option>
                @foreach($taxonomies as $tax)
                    <option value="{{ $tax->id }}"
                            data-hierarchical="{{ $tax->hierarchical ? 'true' : 'false' }}"
                            {{ (string) $currentTaxonomyId === (string) $tax->id ? 'selected' : '' }}>
                        {{ $tax->name }}
                    </option>
                @endforeach
            </select>
            @error('taxonomy_id') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label for="name">Nome *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $term->name) }}" required>
            @error('name') <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="admin-form-row">
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $term->slug) }}" required>
            @error('slug') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label for="order">Ordem</label>
            <input type="number" name="order" id="order" value="{{ old('order', $term->order ?? 0) }}" min="0">
            <small>Menor número aparece primeiro</small>
            @error('order') <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="form-group" id="parent-group" style="display: none;">
        <label for="parent_id">Termo Pai</label>
        <select name="parent_id" id="parent_id">
            <option value="">-- Nenhum --</option>
            @foreach($parentTerms as $parent)
                {{-- Evita que um termo seja pai dele mesmo na edição --}}
                @if(!$isEdit || $parent->id !== $term->id)
                    <option value="{{ $parent->id }}" {{ (string) old('parent_id', $term->parent_id) === (string) $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endif
            @endforeach
        </select>
        <small>Apenas para taxonomias hierárquicas</small>
        @error('parent_id') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="description">Descrição</label>
        <textarea name="description" id="description" rows="3">{{ old('description', $term->description) }}</textarea>
        @error('description') <small class="error">{{ $message }}</small> @enderror
    </div>

    <x-hook
        :name="$isEdit ? 'admin.edit_term' : 'admin.create_term'"
        :params="['term' => $term]"
        desc="No formulário de criação/edição de termo"
    />

    <div class="buttons">
        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</form>

@if($isEdit)
    <x-lost-changes-warn selector="#term_form" />
@endif

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

        const taxonomySelect = document.getElementById('taxonomy_id');
        const parentGroup = document.getElementById('parent-group');
        const parentSelect = document.getElementById('parent_id');

        function checkHierarchical() {
            if (!taxonomySelect || !parentGroup) return;
            const selectedOption = taxonomySelect.options[taxonomySelect.selectedIndex];
            const isHierarchical = selectedOption && selectedOption.getAttribute('data-hierarchical') === 'true';

            parentGroup.style.display = isHierarchical ? 'block' : 'none';
            if (!isHierarchical && parentSelect) {
                parentSelect.value = '';
            }
        }

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
