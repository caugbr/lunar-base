@extends('admin.layout')

@section('header_title', 'Posts')
@section('header_subtitle', 'Gerencie as publicações do blog')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-files class="lucid-icon" /> Lista de Posts</h2>
        <a href="{{ route('admin.posts.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Novo Post</span>
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.posts.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <input type="text" name="title" value="{{ request('title') }}" class="admin-filter-input" placeholder="Buscar por título...">
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
            <div class="admin-filter-group">
                <select name="featured" class="admin-filter-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Destacados</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Não destacados</option>
                </select>
            </div>
            <div class="admin-filter-group">
                <select name="sticky" class="admin-filter-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('sticky') === '1' ? 'selected' : '' }}>Fixados</option>
                    <option value="0" {{ request('sticky') === '0' ? 'selected' : '' }}>Não fixados</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.posts.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Autor</th>
                    <th>Publicado em</th>
                    <th>Destaque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td>
                        {{ $post->title }}
                        @if($post->sticky)
                            <span class="admin-badge admin-badge-active" title="Fixado no topo">FIX</span>
                        @endif
                    </td>
                    <td><code>{{ $post->slug }}</code></td>
                    <td><span class="{{ $post->status_badge }}">{{ $post->status_label }}</span></td>
                    <td>{{ $post->author_name }}</td>
                    <td>{{ $post->published_at_formatted }}</td>
                    <td>
                        @if($post->featured)
                            <x-lucide-star class="lucid-icon" style="color: #f59e0b;" />
                        @else
                            <span class="admin-text-muted">—</span>
                        @endif
                    </td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ $post->url }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" target="_blank">
                                <x-lucide-external-link class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Remover este post?')">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="admin-text-center admin-text-muted">Nenhum post cadastrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $posts->appends(request()->query())->links() }}
    </div>
</div>
@endsection
