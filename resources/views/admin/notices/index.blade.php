@extends('admin.layout')

@section('header_title', 'Avisos do Sistema')
@section('header_subtitle', 'Gerencie mensagens e alertas direcionados aos usuários do painel')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-bell class="lucid-icon" /> Lista de Avisos</h2>
        <a href="{{ route('admin.notices.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> Novo Aviso
        </a>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Mensagem</th>
                    <th>Destinatários</th>
                    <th>Status</th>
                    <th>Leituras</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notices as $notice)
                <tr>
                    <td>{{ $notice->id }}</td>
                    <td>
                        <span class="notice-type-tag notice-type-{{ $notice->type }}">
                            {{ ucfirst($notice->type) }}
                        </span>
                    </td>
                    <td class="notice-message-cell">
                        <div class="notice-message-preview">
                            {!! strip_tags($notice->message, '<a><strong><em><code>') !!}
                        </div>
                    </td>
                    <td>
                        @if($notice->target_type === 'all')
                            <span class="admin-badge admin-badge-neutral">Todos os Usuários</span>
                        @elseif($notice->target_type === 'roles')
                            <div class="notice-targets-list">
                                @foreach($notice->target_values ?? [] as $role)
                                    <span class="admin-badge admin-badge-role">{{ $role }}</span>
                                @endforeach
                            </div>
                        @elseif($notice->target_type === 'users')
                            <span class="admin-badge admin-badge-info">
                                {{ count($notice->target_values ?? []) }} usuário(s)
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($notice->is_active)
                            <span class="admin-badge admin-badge-active">
                                <x-lucide-check class="lucid-icon" /> Ativo
                            </span>
                        @else
                            <span class="admin-badge admin-badge-inactive">
                                <x-lucide-x class="lucid-icon" /> Inativo
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="notice-read-count" title="Usuários que fecharam o aviso">
                            <x-lucide-eye-off class="lucid-icon" /> {{ $notice->dismissals_count ?? $notice->dismissals()->count() }}
                        </span>
                    </td>
                    <td>{{ $notice->created_at->format('d/m/Y H:i') }}</td>
                    <td class="admin-actions">
                        <a href="{{ route('admin.notices.edit', $notice->id) }}" class="admin-btn admin-btn-secondary" title="Editar">
                            <x-lucide-pencil class="lucid-icon" />
                        </a>
                        <form method="POST" action="{{ route('admin.notices.destroy', $notice->id) }}" class="inline-form" data-confirm="Remover este aviso?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger" title="Excluir">
                                <x-lucide-trash-2 class="lucid-icon" />
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="admin-empty-list">
                            <div>
                                <x-lucide-bell-off class="lucid-icon" />
                            </div>
                            <h3>Nenhum aviso cadastrado</h3>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $notices->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .notice-type-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .notice-type-info { background: #eff6ff; color: #1d4ed8; }
    .notice-type-warning { background: #fffbeb; color: #b45309; }
    .notice-type-error { background: #fef2f2; color: #b91c1c; }
    .notice-type-success { background: #f0fdf4; color: #15803d; }

    .notice-message-cell {
        max-width: 300px;
    }
    .notice-message-preview {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.875rem;
    }
    .notice-targets-list {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    .notice-read-count {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.85rem;
        color: var(--color-text-muted);
    }
    .inline-form {
        display: inline;
    }
</style>
@endpush
