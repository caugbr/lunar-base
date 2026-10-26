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
                <code>doAction&#40;'user_registered', $user&#41;;</code>
                <p style="margin-top: 6px; font-size: 0.8rem; opacity: 0.85;">Para escutar no seu plugin:</p>
                <code>addAction&#40;'user_registered', function($user) { ... }&#41;;</code>
            </div>
            <div class="usage-card">
                <h5><x-lucide-filter class="lucid-icon" /> Filtro (applyFilters)</h5>
                <p>Intercepta, modifica e retorna um valor alterado:</p>
                <code>$data = applyFilters&#40;'before_save_post', $data&#41;;</code>
                <p style="margin-top: 6px; font-size: 0.8rem; opacity: 0.85;">Para filtrar no seu plugin:</p>
                <code>addFilter&#40;'before_save_post', function($data) { return $data; }&#41;;</code>
            </div>
        </div>
    </div>

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
