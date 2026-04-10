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

    <table class="admin-table">
        <thead>
            <tr>
                {{-- <th>ID</th> --}}
                <th>Título</th>
                <th>Slug</th>
                <th>Classificação</th>
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
                <td>
                    @foreach($page->terms->groupBy('taxonomy.name') as $taxonomyName => $terms)
                        <strong>{{ $taxonomyName }}:</strong>
                        @foreach($terms as $term)
                            <span class="term-badge">{{ $term->name }}</span>
                        @endforeach
                        <br>
                    @endforeach
                </td>
                <td><span class="{{ $page->status_badge }}">{{ $page->status_label }}</span></td>
                <td>{{ $page->author_name }}</td>
                <td>{{ $page->created_at->format('d/m/Y H:i') }}</td>
                <td class="admin-actions">
                    <a href="{{ $page->public_url }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" target="_blank">
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
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="admin-text-center admin-text-muted">Nenhuma página cadastrada</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-pagination">
        {{ $pages->links() }}
    </div>
</div>
@endsection
