@extends('admin.layout')

@section('header_title', 'Novo Termo')
@section('header_subtitle', 'Crie um novo termo')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tag class="lucid-icon" /> Novo Termo</h2>
        <a href="{{ route('admin.terms.index', ['taxonomy_id' => $selectedTaxonomy->id ?? null]) }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.terms.store') }}">
        @csrf

        <div class="admin-form-row">
            <div class="form-group">
                <label for="taxonomy_id">Taxonomia *</label>
                <select name="taxonomy_id" id="taxonomy_id" required>
                    <option value="">Selecione</option>
                    @foreach($taxonomies as $tax)
                        <option value="{{ $tax->id }}" {{ old('taxonomy_id', $selectedTaxonomy->id ?? '') == $tax->id ? 'selected' : '' }}>
                            {{ $tax->name }}
                        </option>
                    @endforeach
                </select>
                @error('taxonomy_id') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                @error('slug') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="order">Ordem</label>
                <input type="number" name="order" id="order" value="{{ old('order', 0) }}" min="0">
                <small>Menor número aparece primeiro</small>
            </div>
        </div>

        <div class="form-group" id="parent-group" style="display: none;">
            <label for="parent_id">Termo Pai</label>
            <select name="parent_id" id="parent_id">
                <option value="">-- Nenhum --</option>
                @foreach($parentTerms as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            <small>Apenas para taxonomias hierárquicas</small>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3">{{ old('description') }}</textarea>
            @error('description') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Slug automático
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.dataset.manuallyEdited) {
            slugField.value = slugify(this.value);
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });

    // Mostrar/esconder campo de termo pai baseado na taxonomia selecionada
    const taxonomySelect = document.getElementById('taxonomy_id');
    const parentGroup = document.getElementById('parent-group');
    const parentSelect = document.getElementById('parent_id');

    function checkHierarchical() {
        const selectedOption = taxonomySelect.options[taxonomySelect.selectedIndex];
        const isHierarchical = selectedOption.getAttribute('data-hierarchical') === 'true';

        parentGroup.style.display = isHierarchical ? 'block' : 'none';
        if (!isHierarchical) {
            parentSelect.value = '';
        }
    }

    // Adiciona atributo data-hierarchical às options
    @foreach($taxonomies as $tax)
        var option = document.querySelector('#taxonomy_id option[value="{{ $tax->id }}"]');
        if (option) {
            option.setAttribute('data-hierarchical', '{{ $tax->hierarchical ? 'true' : 'false' }}');
        }
    @endforeach

    taxonomySelect.addEventListener('change', checkHierarchical);
    checkHierarchical();

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
