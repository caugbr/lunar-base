@extends('admin.layout')
@section('header_title', 'Hooks do Sistema')
@section('header_subtitle', 'Pontos de extensao disponiveis para plugins e temas')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/hooks.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-fishing-hook class="lucid-icon" /> Hooks do sistema</h2>
    </div>

    <div class="hooks-explanation">
        <h3><x-lucide-info class="lucid-icon" /> O que sao Hooks?</h3>
        <p>
            Hooks (ou Ganchos) são pontos de ancoragem posicionados nos arquivos de visão (Blade) do tema público ou do painel administrativo. Eles permitem que plugins injetem conteúdo HTML, estilos CSS, scripts JavaScript ou alterem variáveis de forma transparente e desacoplada. Se o hook inclui parâmetros, eles serão enviados para a função atrelada, como um array associativo.
        </p>

        <h4>Como Usar</h4>
        <div class="hooks-usage-grid">
            <div class="usage-card">
                <h5><x-lucide-zap class="lucid-icon" /> Hook de Ação</h5>
                <p>Reserva espaço para plugins inserirem conteúdo extra. É auto-fechado:</p>
                <code>&lt;x-hook name="post.footer_end" :params="['post' => $post]" /&gt;</code>
            </div>
            <div class="usage-card">
                <h5><x-lucide-filter class="lucid-icon" /> Hook de Filtro</h5>
                <p>Renderiza conteúdo padrão caso nenhum plugin esteja atrelado:</p>
                <code>&lt;x-hook name="public.main_menu" desc="Menu Principal"&gt;...&lt;/x-hook&gt;</code>
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
                        @if($hook['params'])
                            <code class="params-code">{{ $hook['params'] }}</code>
                        @else
                            <span class="admin-text-muted">—</span>
                        @endif
                    </td>
                    <td class="hook-description">
                        {{ $hook['description'] }}
                        @if($hook['file'])
                            <br><small class="admin-text-muted">{{ $hook['file'] }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-text-center admin-text-muted" style="padding: 30px;">
                        Nenhum hook descoberto. Certifique-se de que o HookDiscoverer está configurado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
