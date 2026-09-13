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

    <!-- Abas de Status & Lixeira -->
    <div class="content-status-bar">
        <div class="status-tabs-group">
            <a href="{{ route('admin.posts.index') }}"
            class="status-tab-btn {{ !$isTrash && !request('status') ? 'active' : '' }}">
                <x-lucide-circle-check class="lucid-icon" />
                Todos <span class="tab-badge">{{ $counts['all'] }}</span>
            </a>

            <a href="{{ route('admin.posts.index', ['status' => 'published']) }}"
            class="status-tab-btn {{ request('status') === 'published' ? 'active' : '' }}">
                <x-lucide-circle class="lucid-icon" />
                Publicados <span class="tab-badge">{{ $counts['published'] }}</span>
            </a>

            <a href="{{ route('admin.posts.index', ['status' => 'draft']) }}"
            class="status-tab-btn {{ request('status') === 'draft' ? 'active' : '' }}">
                <x-lucide-circle-dashed class="lucid-icon" />
                Rascunhos <span class="tab-badge">{{ $counts['draft'] }}</span>
            </a>

            <a href="{{ route('admin.posts.index', ['view' => 'trash']) }}"
            class="status-tab-btn tab-trash {{ $isTrash ? 'active' : '' }}">
                <x-lucide-trash class="lucid-icon" />
                <span>Lixeira</span>
                <span class="tab-badge">{{ $counts['trash'] }}</span>
            </a>
        </div>

        @if($isTrash && $counts['trash'] > 0)
            <form method="POST" action="{{ route('admin.posts.empty_trash') }}" data-confirm="Tem certeza que deseja esvaziar toda a lixeira? Essa ação não pode ser desfeita.">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">
                    <x-lucide-trash-2 class="lucid-icon" /> Esvaziar Lixeira
                </button>
            </form>
        @endif
    </div>

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
                    {{-- <td class="admin-actions">
                        <div>
                            <a href="{{ $post->url }}" class="admin-btn admin-btn-secondary" target="_blank">
                                <x-lucide-external-link class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="admin-btn admin-btn-secondary">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <x-hook name="admin.post_actions" :params="['post' => $post]" desc="Actions na listagem de posts" />
                            <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" data-confirm="Remover este post?" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td> --}}
                    <td class="admin-actions">
                        <div>
                            @if($isTrash)
                                {{-- BOTÃO RESTAURAR --}}
                                <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-secondary" title="Restaurar este post">
                                        <x-lucide-rotate-ccw class="lucid-icon" />
                                    </button>
                                </form>

                                {{-- BOTÃO EXCLUIR DEFINITIVO (PURGE) --}}
                                <form method="POST" action="{{ route('admin.posts.purge', $post->id) }}" data-confirm="Excluir este post definitivamente? Essa ação NÃO pode ser desfeita." style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-danger" title="Excluir definitivamente">
                                        <x-lucide-trash-2 class="lucid-icon" />
                                    </button>
                                </form>
                            @else
                                {{-- BOTÕES NORMAIS --}}
                                <a href="{{ $post->url }}" class="admin-btn admin-btn-secondary" target="_blank" title="Visitar">
                                    <x-lucide-external-link class="lucid-icon" />
                                </a>
                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="admin-btn admin-btn-secondary" title="Editar">
                                    <x-lucide-pencil class="lucid-icon" />
                                </a>
                                <x-hook name="admin.post_actions" :params="['post' => $post]" desc="Actions na listagem de posts" />

                                {{-- BOTÃO MOVER PARA A LIXEIRA --}}
                                <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" data-confirm="Mover este post para a lixeira?" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-danger" title="Mover para a lixeira">
                                        <x-lucide-trash-2 class="lucid-icon" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="admin-empty-list">
                            <div>
                                <x-lucide-circle-off class="lucid-icon" />
                            </div>
                            <h3>Nenhum post encontrado</h3>
                        </div>
                    </td>
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

@push('styles')
<style>
/* ==========================================================================
   BARRA DE ABAS DE STATUS E LIXEIRA (Lunar Base UI)
   ========================================================================== */

.content-status-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    margin-bottom: 20px;
    background: #f8fafc;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 8px;
    flex-wrap: wrap;
}

.status-tabs-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Links em formato de pílulas */
.status-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: #ffffff;
    border: 1px solid var(--color-border, #cbd5e1);
    border-radius: 20px;
    color: #475569;
    font-size: 0.815rem;
    font-weight: 500;
    text-decoration: none;
    line-height: 1;
    transition: all 0.15s ease;
}

.status-tab-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* Aba Ativa */
.status-tab-btn.active {
    background: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
}

/* Badge Numérico */
.status-tab-btn .tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.07);
    color: inherit;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
}

.status-tab-btn.active .tab-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* Ícones dentro dos botões */
.status-tab-btn .lucid-icon {
    width: 13px;
    height: 13px;
}

/* Estilo especial para a Lixeira */
.status-tab-btn.tab-trash:hover {
    color: #dc2626;
    border-color: #fca5a5;
    background: #fef2f2;
}

.status-tab-btn.tab-trash.active {
    background: #dc2626;
    border-color: #dc2626;
    color: #ffffff;
}

.status-tab-btn.tab-trash.active .tab-badge {
    background: rgba(255, 255, 255, 0.3);
}
</style>
@endpush

