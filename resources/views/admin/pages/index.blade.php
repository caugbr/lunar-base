@extends('admin.layout')

@section('header_title', 'Páginas')
@section('header_subtitle', 'Gerencie as páginas públicas')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Lista de Páginas</h2>
        <a href="{{ route('admin.pages.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Nova Página</span>
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.pages.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <input type="text" name="title" value="{{ request('title') }}" class="admin-filter-input" placeholder="Buscar por título...">
            </div>
            <div class="admin-filter-group">
                <select name="namespace" class="admin-filter-select">
                    <option value="">Todos os namespaces</option>
                    @foreach($namespaces as $namespace)
                        <option value="{{ $namespace }}" {{ request('namespace') == $namespace ? 'selected' : '' }}>
                            {{ $namespace }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-group">
                <select name="status" class="admin-filter-select">
                    <option value="">Todos os statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>
            <div class="admin-filter-group">
                <select name="author_id" class="admin-filter-select">
                    <option value="">Todos os autores</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        {{-- </div>
        <div class="admin-filters-row"> --}}
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    {{-- <th>ID</th> --}}
                    <th>Título</th>
                    <th>Slug</th>
                    <th>Namespace</th>
                    <th>Status</th>
                    <th>Autor</th>
                    <th>Criada em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr>
                    {{-- <td>{{ $page->id }}</td> --}}
                    <td>{{ $page->title }}</td>
                    <td><code>{{ $page->slug }}</code></td>
                    <td><code>{{ $page->namespace }}</code></td>
                    <td><span class="{{ $page->status_badge }}">{{ $page->status_label }}</span></td>
                    <td>{{ $page->author_name }}</td>
                    <td>{{ $page->created_at->format('d/m/Y H:i') }}</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ $page->url }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" target="_blank">
                                <x-lucide-external-link class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Remover esta página?')">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="admin-text-center admin-text-muted">Nenhuma página cadastrada</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $pages->appends(request()->query())->links() }}
    </div>
</div>
@endsection
