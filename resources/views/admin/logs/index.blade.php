@extends('admin.layout')

@section('header_title', 'Logs de Auditoria')
@section('header_subtitle', 'Monitore as ações executadas no painel administrativo')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-list-checks class="lucid-icon" /> Histórico de Ações</h2>
        <!-- Sem botão de "Novo" já que logs são apenas para leitura -->
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.logs.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <!-- Filtro por Ação -->
            <div class="admin-filter-group">
                <input type="text" name="action" value="{{ request('action') }}" class="admin-filter-input" placeholder="Buscar por ação...">
            </div>

            <!-- Filtro por Usuário -->
            <div class="admin-filter-group">
                <input type="text" name="user_name" value="{{ request('user_name') }}" class="admin-filter-input" placeholder="Buscar por usuário...">
            </div>

            <!-- Filtro por Categoria Dinâmico -->
            <div class="admin-filter-group">
                <select name="category" class="admin-filter-select">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Ações dos Filtros -->
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.logs.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 180px;">Data / Hora</th>
                    <th style="width: 150px;">Usuário</th>
                    <th style="width: 130px;">Categoria</th>
                    <th>Ação Executada</th>
                    <th>Origem (Referrer)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr title="IP: {{ $log->ip_address ?? 'N/D' }} &#10;Dispositivo: {{ $log->user_agent ?? 'N/D' }}">
                    <td class="admin-text-muted">
                        <x-lucide-clock class="lucid-icon" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 4px;" />
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td>
                        <strong>{{ $log->user_name ?? 'Sistema/Anônimo' }}</strong>
                    </td>
                    <td>
                        <span class="admin-badge admin-badge-secondary" style="background-color: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                            {{ strtoupper($log->category) }}
                        </span>
                    </td>
                    <td>
                        <span style="color: #1e293b; font-weight: 500;">{{ $log->action }}</span>
                    </td>
                    <td class="admin-text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        @if($log->referrer)
                            <small class="admin-code" title="{{ $log->referrer }}">{{ preg_replace('/^https?:\/\/[^\/]+/', '', $log->referrer) }}</small>
                        @else
                            <span class="admin-text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="admin-text-center admin-text-muted" style="padding: 40px 0;">
                        <x-lucide-info class="lucid-icon" style="margin-bottom: 8px; vertical-align: middle;" />
                        Nenhum log de auditoria encontrado com os filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação mantendo os filtros aplicados na URL -->
    <div class="admin-pagination">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection