@extends('admin.layout')

@section('header_title', 'Editar Termo')
@section('header_subtitle', 'Modifique os dados do termo')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tag class="lucid-icon" /> Editar: {{ $term->name }}</h2>
        <a href="{{ route('admin.terms.index', ['taxonomy_id' => $term->taxonomy_id]) }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.terms.update', $term->id) }}">
        @csrf
        @method('PUT')

        <div class="admin-form-row">
            <div class="form-group">
                <label for="taxonomy_id">Taxonomia *</label>
                <select name="taxonomy_id" id="taxonomy_id" required>
                    <option value="">Selecione</option>
                    @foreach($taxonomies as $tax)
                        <option value="{{ $tax->id }}" {{ old('taxonomy_id', $term->taxonomy_id) == $tax->id ? 'selected' : '' }}>
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
                <input type="number" name="order" id="order" value="{{ old('order', $term->order) }}" min="0">
            </div>
        </div>

        <div class="form-group" id="parent-group" style="display: none;">
            <label for="parent_id">Termo Pai</label>
            <select name="parent_id" id="parent_id">
                <option value="">-- Nenhum --</option>
                @foreach($parentTerms as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $term->parent_id) == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3">{{ old('description', $term->description) }}</textarea>
            @error('description') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Atualizar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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

    @foreach($taxonomies as $tax)
        var option = document.querySelector('#taxonomy_id option[value="{{ $tax->id }}"]');
        if (option) {
            option.setAttribute('data-hierarchical', '{{ $tax->hierarchical ? 'true' : 'false' }}');
        }
    @endforeach

    taxonomySelect.addEventListener('change', checkHierarchical);
    checkHierarchical();
</script>
@endpush
