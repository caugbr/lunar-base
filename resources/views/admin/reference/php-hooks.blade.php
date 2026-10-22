@extends('admin.layout')
@section('header_title', 'Hooks PHP do Sistema')
@section('header_subtitle', 'Pontos de extensão de lógica e manipulação de dados no backend')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/hooks.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-code class="lucid-icon" /> Referência de Hooks PHP</h2>

        <a href="{{ route('admin.hooks.sync') }}"
           class="admin-btn admin-btn-secondary"
           style="display: inline-flex; align-items: center; gap: 6px;"
           title="Escanear e compilar lista de hooks">
            <x-lucide-rotate-cw class="lucid-icon" style="width: 16px; height: 16px;" />
            Sincronizar Hooks
        </a>
    </div>

    <div class="hooks-explanation">
        <h3><x-lucide-info class="lucid-icon" /> O que são Hooks PHP?</h3>
        <p>
            Diferente dos ganchos de template (Blade), os Hooks PHP ocorrem durante o processamento de regras de negócio no backend (controllers, models, services ou rotas). Eles permitem que plugins executem ações adicionais em momentos-chave do sistema ou interceptem e modifiquem variáveis, coleções e dados antes que sejam salvos ou renderizados.
        </p>

        <h4>Como Usar</h4>
        <div class="hooks-usage-grid">
            <div class="usage-card">
                <h5><x-lucide-zap class="lucid-icon" /> Ação (doAction)</h5>
                <p>Disparada em momentos pontuais. Não retorna valor:</p>
                <code>doAction('user_registered', $user);</code>
                <p style="margin-top: 6px; font-size: 0.8rem; opacity: 0.85;">Para escutar no seu plugin:</p>
                <code>addAction('user_registered', function($user) { ... });</code>
            </div>
            <div class="usage-card">
                <h5><x-lucide-filter class="lucid-icon" /> Filtro (applyFilters)</h5>
                <p>Intercepta, modifica e retorna um valor alterado:</p>
                <code>$data = applyFilters('before_save_post', $data);</code>
                <p style="margin-top: 6px; font-size: 0.8rem; opacity: 0.85;">Para filtrar no seu plugin:</p>
                <code>addFilter('before_save_post', function($data) { return $data; });</code>
            </div>
        </div>
    </div>

    {{-- <div class="filter">
        Mostrar:
        <label><input type="radio" name="show" value="all" checked> Todos</label>
        <label><input type="radio" name="show" value="admin"> Administração</label>
        <label><input type="radio" name="show" value="front"> Outros / Geral</label>
    </div> --}}

    <div class="table-wrap" style="margin-top: 1.5rem;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Nome do Hook</th>
                    <th style="width: 10%;">Tipo</th>
                    <th style="width: 25%;">Parâmetros</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hooks as $hook)
                <tr>
                    <td>
                        <code class="hook-name">{{ $hook['name'] }}</code>
                    </td>
                    <td>
                        @if($hook['type'] === 'filter')
                            <span class="admin-badge admin-badge-info">
                                <x-lucide-filter class="lucid-icon" style="width: 12px; height: 12px;" /> Filtro
                            </span>
                        @else
                            <span class="admin-badge admin-badge-active">
                                <x-lucide-zap class="lucid-icon" style="width: 12px; height: 12px;" /> Ação
                            </span>
                        @endif
                    </td>
                    <td class="hook-params">
                        @if(!empty($hook['params']))
                            <code class="params-code">{{ $hook['params'] }}</code>
                        @else
                            <span class="admin-text-muted">—</span>
                        @endif
                    </td>
                    <td class="hook-description">
                        @if($hook['description'] && $hook['description'] !== 'Sem descrição')
                            {{ $hook['description'] }}
                        @else
                            <span class="admin-text-muted" style="font-style: italic;">Sem descrição</span>
                        @endif

                        @if(!empty($hook['file']))
                            <br><small class="admin-text-muted">{{ $hook['file'] }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-empty-list admin-text-muted" style="padding: 30px;">
                        Nenhum hook PHP descoberto. Execute <code>php artisan hooks:scan</code> no terminal para compilar a lista.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- @once
@push('styles')
<style>
    .filter {
        background: var(--color-bg-dark);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
    }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterRadios = document.querySelectorAll('input[name="show"]');
    const rows = document.querySelectorAll('.admin-table tbody tr');

    filterRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            const selectedFilter = this.value; // 'all', 'admin' ou 'front'

            rows.forEach(row => {
                const hookNameElement = row.querySelector('.hook-name');

                if (!hookNameElement) return;

                const hookName = hookNameElement.textContent.trim();
                const isAdmin = hookName.startsWith('admin.');

                if (selectedFilter === 'all') {
                    row.style.display = '';
                } else if (selectedFilter === 'admin') {
                    row.style.display = isAdmin ? '' : 'none';
                } else if (selectedFilter === 'front') {
                    row.style.display = !isAdmin ? '' : 'none';
                }
            });
        });
    });
});
</script>
@endpush
@endonce --}}
