@extends('admin.layout')

@section('header_title', 'Taxonomias')
@section('header_subtitle', 'Gerencie os tipos de classificação')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tags class="lucid-icon" /> Lista de Taxonomias</h2>
        <a href="{{ route('admin.taxonomies.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> Nova Taxonomia
        </a>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Hierárquica</th>
                    <th>Termos</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taxonomies as $taxonomy)
                <tr>
                    <td>{{ $taxonomy->id }}</td>
                    <td>{{ $taxonomy->name }}</td>
                    <td><code>{{ $taxonomy->slug }}</code></td>
                    <td class="text-center">
                        @if($taxonomy->hierarchical)
                            <span class="admin-badge admin-badge-active">
                                <x-lucide-check class="lucid-icon" /> Sim
                            </span>
                        @else
                            <span class="admin-badge admin-badge-inactive">
                                <x-lucide-x class="lucid-icon" /> Não
                            </span>
                        @endif
                    </td>
                    <td>{{ $taxonomy->terms()->count() }}</td>
                    <td>{{ $taxonomy->created_at->format('d/m/Y H:i') }}</td>
                    <td class="admin-actions">
                        <a href="{{ route('admin.taxonomies.edit', $taxonomy->id) }}" class="admin-btn admin-btn-secondary">
                            <x-lucide-pencil class="lucid-icon" />
                        </a>
                        <a href="{{ route('admin.terms.index', ['taxonomy_id' => $taxonomy->id]) }}" class="admin-btn admin-btn-secondary">
                            <x-lucide-tags class="lucid-icon" />
                        </a>
                        <form method="POST" action="{{ route('admin.taxonomies.destroy', $taxonomy->id) }}" style="display: inline;" data-confirm="Remover esta taxonomia?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger">
                                <x-lucide-trash-2 class="lucid-icon" />
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="admin-empty-list">
                            <div>
                                <x-lucide-circle-off class="lucid-icon" />
                            </div>
                            <h3>Nenhuma taxonomia cadastrada</h3>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $taxonomies->links() }}
    </div>
</div>
@endsection
