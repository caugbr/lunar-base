@extends('admin.layout')

@section('header_title', 'Editar Taxonomia')
@section('header_subtitle', 'Modifique os dados da taxonomia')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tags class="lucid-icon" /> Editar: {{ $taxonomy->name }}</h2>
        <a href="{{ route('admin.taxonomies.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.taxonomies.update', $taxonomy->id) }}" id="tax_form">
        @csrf
        @method('PUT')

        <div class="admin-form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $taxonomy->name) }}" required>
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $taxonomy->slug) }}" required>
                @error('slug') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3">{{ old('description', $taxonomy->description) }}</textarea>
            @error('description') <small class="error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            {{-- <label class="checkbox-label">
                <input type="checkbox" name="hierarchical" value="1" {{ old('hierarchical', $taxonomy->hierarchical) ? 'checked' : '' }}>
                <span>Hierárquica</span>
            </label> --}}
            <label for="hierarchical">Hierárquica?</label>
            <x-switch name="hierarchical" id="hierarchical" checked="{{ old('hierarchical', $taxonomy->hierarchical) ? true : false }}" active="Sim" inactive="Não" />
            <small>Termos podem ter sub-termos (ex: Categorias com subcategorias)</small>
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Atualizar
            </button>
        </div>
    </form>
</div>

<x-lost-changes-warn selector="#tax_form" />
@endsection
