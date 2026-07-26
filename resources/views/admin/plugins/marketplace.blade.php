@extends('admin.layout')

@section('header_title', 'Marketplace de Plugins')
@section('header_subtitle', 'Explore, baixe e instale novas extensões para o Lunar Base')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-shopping-bag class="lucid-icon" /> Plugins Disponíveis</h2>
        <div style="display: flex; gap: 0.5rem; align-items: center;">

            {{-- Botão Voltar para Lista Local --}}
            <a href="{{ route('admin.plugins.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" />
                Plugins Instalados
            </a>

            {{-- Formulário de Recarregar Catálogo --}}
            <form method="POST" action="{{ route('admin.plugins.marketplace.refresh') }}" style="display: inline;">
                @csrf
                <button type="submit" class="admin-btn admin-btn-secondary" title="Atualizar catálogo remoto">
                    <x-lucide-refresh-cw class="lucid-icon" />
                    Atualizar Catálogo
                </button>
            </form>

            {{-- Botão de Submissão do Formulário de Instalação em Lote --}}
            <button type="submit" form="batch-install-form" class="admin-btn admin-btn-primary">
                <x-lucide-download class="lucid-icon" />
                Instalar Selecionados
            </button>
        </div>
    </div>

    {{-- Formulário Principal de Instalação --}}
    <form id="batch-install-form" method="POST" action="{{ route('admin.plugins.marketplace.install') }}">
        @csrf

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all-plugins" onclick="toggleSelectAll(this)" title="Marcar/Desmarcar todos os disponíveis">
                        </th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Versão</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plugins as $plugin)
                    <tr>
                        <td style="text-align: center;">
                            @if(! $plugin['is_installed'])
                                {{-- Envia as informações do plugin selecionado para a controller --}}
                                <input type="checkbox"
                                       name="selected_plugins[]"
                                       value="{{ json_encode(['name' => $plugin['name'], 'download_url' => $plugin['download_url']]) }}"
                                       class="plugin-checkbox"
                                       style="cursor: pointer; width: 16px; height: 16px;">
                            @else
                                <x-lucide-check class="lucid-icon" style="color: #10b981;" title="Já Instalado" />
                            @endif
                        </td>
                        <td>
                            <strong>{{ $plugin['name'] }}</strong>
                        </td>
                        <td style="max-width: 380px; white-space: normal;">
                            <span class="admin-text-muted" style="font-size: 0.875rem;">
                                {{ $plugin['description'] ?? 'Nenhuma descrição fornecida.' }}
                            </span>
                        </td>
                        <td>
                            <code>v{{ $plugin['version'] }}</code>
                        </td>
                        <td>
                            @if($plugin['is_active'])
                                <span class="admin-badge admin-badge-active">
                                    Ativo
                                </span>
                            @elseif($plugin['is_installed'])
                                <span class="admin-badge admin-badge-suspended">
                                    Instalado (Inativo)
                                </span>
                            @else
                                <span class="admin-badge" style="background-color: #e5e7eb; color: #4b5563;">
                                    Não Instalado
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="admin-text-center admin-text-muted" style="padding: 2rem;">
                            Nenhum plugin disponível no momento ou falha ao carregar o catálogo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Função para marcar/desmarcar todos os checkboxes que não estão desabilitados
    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.plugin-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
    }
</script>
@endpush
