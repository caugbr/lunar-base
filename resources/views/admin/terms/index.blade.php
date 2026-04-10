@extends('admin.layout')

@section('header_title', 'Termos')
@section('header_subtitle', 'Gerencie os termos das taxonomias')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tag class="lucid-icon" /> Lista de Termos</h2>
        <a href="{{ route('admin.terms.create', ['taxonomy_id' => $taxonomyId]) }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> Novo Termo
        </a>
    </div>

    <!-- Filtro por taxonomia -->
    <form method="GET" action="{{ route('admin.terms.index') }}" class="admin-filters" style="margin-bottom: 1rem;">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <select name="taxonomy_id" class="admin-filter-select" onchange="this.form.submit()">
                    <option value="">Todas as taxonomias</option>
                    @foreach($taxonomies as $tax)
                        <option value="{{ $tax->id }}" {{ $taxonomyId == $tax->id ? 'selected' : '' }}>
                            {{ $tax->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <a href="{{ route('admin.terms.index') }}" class="admin-btn admin-btn-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Slug</th>
                <th>Taxonomia</th>
                <th>Termo Pai</th>
                <th>Ordem</th>
                <th>Páginas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terms as $term)
            <tr>
                <td>{{ $term->id }}</td>
                <td>{{ $term->name }}</td>
                <td><code>{{ $term->slug }}</code></td>
                <td>{{ $term->taxonomy->name ?? '-' }}</td>
                <td>{{ $term->parent->name ?? '-' }}</td>
                <td>{{ $term->order }}</td>
                <td>{{ $term->pages()->count() }}</td>
                <td class="admin-actions">
                    <a href="{{ route('admin.terms.edit', $term->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                        <x-lucide-pencil class="lucid-icon" />
                    </a>
                    <form method="POST" action="{{ route('admin.terms.destroy', $term->id) }}" style="display: inline;" onsubmit="return confirm('Remover este termo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;">
                            <x-lucide-trash-2 class="lucid-icon" />
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="admin-text-center admin-text-muted">Nenhum termo cadastrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-pagination">
        {{ $terms->appends(request()->query())->links() }}
    </div>
</div>
@endsection
