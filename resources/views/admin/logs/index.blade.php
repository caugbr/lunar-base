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
                <tr>
                    <td class="admin-text-muted">
                        <x-lucide-clock class="lucid-icon" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 4px;" />
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td title="IP: {{ $log->ip_address ?? 'N/D' }} &#10;Dispositivo: {{ $log->user_agent ?? 'N/D' }}">
                        <strong>{{ $log->user_name ?? 'Sistema/Anônimo' }}</strong>
                    </td>
                    <td>
                        <span class="admin-badge admin-badge-secondary" style="background-color: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                            {{ strtoupper($log->category) }}
                        </span>
                    </td>
                    @php
                    $meta = count($log->metadata) ? json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : false;
                    @endphp
                    <td>
                        <span style="color: #1e293b; font-weight: 500;">{{ $log->action }}</span>
                        @if($meta)
<span class="meta-info">
    <button type="button" popovertarget="meta-{{ $log->id }}">
        <x-lucide-info class="lucid-icon" />
    </button>
    <span id="meta-{{ $log->id }}" popover class="meta-popup">
        <code>{{ $meta }}</code>
    </span>
</span>
                        @endif
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

@push('styles')
<style>
.meta-info {
    position: relative;
    display: inline-block;
    margin-left: 6px;
    vertical-align: middle;
}

.meta-info > button {
    color: #64748b;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    border: 0;
    background-color: rgba(0, 0, 0, 0);
    cursor: pointer;
}

.meta-info > a:hover {
    color: #1e293b;
}

.meta-popup[popover] {
    inset: auto;
    margin: 0;
    min-width: 320px;
    max-width: 580px;
    max-height: 600px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    overflow: auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 12px;
}

.meta-popup[popover]::backdrop {
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(2px);
}

.meta-popup code {
    display: block;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
    font-size: 0.8rem;
    line-height: 1.5;
    color: #334155;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
@endpush
