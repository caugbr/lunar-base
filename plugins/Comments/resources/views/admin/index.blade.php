@extends('admin.layout')

@section('header_title', 'Comentários')
@section('header_subtitle', 'Modere os comentários do site')

@section('content')
<style>
/* ============================================
   BULK ACTIONS
   ============================================ */

.admin-bulk-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm, 1rem);
    padding: var(--space-sm, 1rem);
    background-color: var(--color-bg-card);
    border-bottom: 1px solid var(--color-border);
    margin-bottom: 2rem;
}

.admin-bulk-actions-left {
    display: flex;
    align-items: center;
    gap: var(--space-xs, 0.5rem);
}

.admin-bulk-actions-right {
    display: flex;
    align-items: center;
    gap: var(--space-xs, 0.5rem);
}

.admin-bulk-actions-right .admin-text-muted {
    margin-right: var(--space-xs, 0.5rem);
    font-size: 0.875rem;
}

/* ============================================
   CHECKBOX
   ============================================ */

.admin-checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-xs, 0.5rem);
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--color-text);
    user-select: none;
}

.admin-checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--color-primary);
    cursor: pointer;
}

/* ============================================
   CÉLULA DO AUTOR
   ============================================ */

.comment-author-cell {
    display: flex;
    align-items: center;
    gap: var(--space-sm, 0.75rem);
}

.comment-avatar-small {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background-color: var(--color-bg-dark);
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    border: 1px solid var(--color-border);
    flex-shrink: 0;
}

.comment-author-name {
    font-weight: 600;
    color: var(--color-text);
    font-size: 0.875rem;
}

.comment-author-email {
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

/* ============================================
   CONTEÚDO DO COMENTÁRIO
   ============================================ */

.comment-content-preview {
    font-size: 0.875rem;
    color: var(--color-text);
    line-height: 1.5;
    max-width: 300px;
}

/* ============================================
   LINK PARA POST
   ============================================ */

.admin-link {
    color: var(--color-primary);
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.2s ease;
}

.admin-link:hover {
    color: var(--color-primary-dark, #9D7CFF);
    text-decoration: underline;
}

/* ============================================
   FORMS INLINE (ações da tabela)
   ============================================ */

.admin-inline-form {
    display: inline-flex;
}
</style>

<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-message-square class="lucid-icon" /> Lista de Comentários</h2>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.comments.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <input type="text" name="search" value="{{ request('search') }}" class="admin-filter-input" placeholder="Buscar por autor ou conteúdo...">
            </div>
            <div class="admin-filter-group">
                <select name="status" class="admin-filter-select">
                    <option value="">Todos os status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.comments.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <!-- Ações em massa -->
    <form method="POST" action="{{ route('admin.comments.bulk') }}" id="bulk-form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" id="bulk-status" value="">

        <div class="admin-bulk-actions">
            <div class="admin-bulk-actions-left">
                <label class="admin-checkbox-label">
                    <input type="checkbox" id="select-all" class="admin-checkbox">
                    <span>Selecionar todos</span>
                </label>
            </div>
            <div class="admin-bulk-actions-right">
                <span class="admin-text-muted">Ações em massa:</span>
                <button type="button" class="admin-btn admin-btn-success" onclick="submitBulk('approved')">
                    <x-lucide-check class="lucid-icon" /> Aprovar
                </button>
                <button type="button" class="admin-btn admin-btn-warning" onclick="submitBulk('rejected')">
                    <x-lucide-x class="lucid-icon" /> Rejeitar
                </button>
                <button type="button" class="admin-btn admin-btn-danger" onclick="submitBulkDelete()">
                    <x-lucide-trash-2 class="lucid-icon" /> Excluir
                </button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-table-col-narrow"></th>
                        <th>Autor</th>
                        <th>Conteúdo</th>
                        <th>Em</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $comment->id }}" class="admin-checkbox comment-checkbox">
                        </td>
                        <td>
                            <div class="comment-author-cell">
                                <div class="comment-avatar-small">{{ substr($comment->author_name, 0, 2) }}</div>
                                <div>
                                    <div class="comment-author-name">{{ $comment->author_name }}</div>
                                    <div class="comment-author-email">{{ $comment->author_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="comment-content-preview" title="{{ $comment->content }}">
                                {{ Str::limit($comment->content, 120) }}
                            </div>
                        </td>
                        <td>
                            @if($comment->commentable)
                                <a href="{{ $comment->commentable->url ?? '#' }}" target="_blank" class="admin-link">
                                    {{ Str::limit($comment->commentable->title ?? '—', 40) }}
                                </a>
                            @else
                                <span class="admin-text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="admin-badge admin-badge-{{ $comment->status }}">
                                {{ $statuses[$comment->status] ?? $comment->status }}
                            </span>
                        </td>
                        <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="admin-actions">
                            <div>
                                <form method="POST" action="{{ route('admin.comments.update', $comment->id) }}" class="admin-inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="admin-btn admin-btn-success" title="Aprovar">
                                        <x-lucide-check class="lucid-icon" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.comments.update', $comment->id) }}" class="admin-inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="admin-btn admin-btn-warning" title="Rejeitar">
                                        <x-lucide-x class="lucid-icon" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.comments.destroy', $comment->id) }}" class="admin-inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-danger" onclick="return confirm('Excluir este comentário permanentemente?')" title="Excluir">
                                        <x-lucide-trash-2 class="lucid-icon" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="admin-text-center admin-text-muted">Nenhum comentário encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="admin-pagination">
        {{ $comments->appends(request()->query())->links() }}
    </div>
</div>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('.comment-checkbox').forEach(cb => cb.checked = this.checked);
    });

    function submitBulk(status) {
        const checked = document.querySelectorAll('.comment-checkbox:checked');
        if (checked.length === 0) {
            alert('Selecione pelo menos um comentário.');
            return;
        }
        document.getElementById('bulk-status').value = status;
        document.getElementById('bulk-form').submit();
    }

    function submitBulkDelete() {
        const checked = document.querySelectorAll('.comment-checkbox:checked');
        if (checked.length === 0) {
            alert('Selecione pelo menos um comentário.');
            return;
        }
        if (!confirm('Excluir ' + checked.length + ' comentário(s) permanentemente?')) {
            return;
        }
        // Change form action to bulk delete route
        const form = document.getElementById('bulk-form');
        form.action = '{{ route("admin.comments.bulk-delete") }}';
        form.method = 'POST';
        // Remove the status hidden input since delete doesn't need it
        const statusInput = document.getElementById('bulk-status');
        if (statusInput) statusInput.remove();
        form.submit();
    }
</script>
@endsection
