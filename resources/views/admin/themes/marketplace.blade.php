@extends('admin.layout')

@section('header_title', 'Marketplace de Temas')
@section('header_subtitle', 'Explore, baixe e instale novos temas para o Lunar Base')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-palette class="lucid-icon" /> Temas Disponíveis</h2>
        <div style="display: flex; gap: 0.5rem; align-items: center;">

            {{-- Botão Voltar para Temas Instalados --}}
            <a href="{{ route('admin.themes.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" />
                Temas Instalados
            </a>

            {{-- Formulário de Recarregar Catálogo --}}
            <form method="POST" action="{{ route('admin.themes.marketplace.refresh') }}" style="display: inline;">
                @csrf
                <button type="submit" class="admin-btn admin-btn-secondary" title="Atualizar catálogo remoto">
                    <x-lucide-refresh-cw class="lucid-icon" />
                    Atualizar Catálogo
                </button>
            </form>

            {{-- Botão de Submissão do Formulário de Instalação em Lote --}}
            <button type="submit" form="batch-install-theme-form" class="admin-btn admin-btn-primary">
                <x-lucide-download class="lucid-icon" />
                Instalar Selecionados
            </button>
        </div>
    </div>

    {{-- Formulário Principal de Instalação em Lote --}}
    <form id="batch-install-theme-form" method="POST" action="{{ route('admin.themes.marketplace.install') }}">
        @csrf

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all-themes" onclick="toggleSelectAllThemes(this)" title="Marcar/Desmarcar todos os disponíveis">
                        </th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Versão</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($themes as $theme)
                    <tr>
                        <td style="text-align: center;">
                            @if(! $theme['is_installed'])
                                {{-- Envia as informações do tema selecionado para a controller --}}
                                <input type="checkbox"
                                       name="selected_themes[]"
                                       value="{{ json_encode(['name' => $theme['name'], 'download_url' => $theme['download_url']]) }}"
                                       class="theme-checkbox"
                                       style="cursor: pointer; width: 16px; height: 16px;">
                            @else
                                <x-lucide-check class="lucid-icon" style="color: #10b981;" title="Já Instalado" />
                            @endif
                        </td>
                        <td>
                            <strong>{{ $theme['name'] }}</strong>
                        </td>
                        <td style="max-width: 380px; white-space: normal;">
                            <span class="admin-text-muted" style="font-size: 0.875rem;">
                                {{ $theme['description'] ?? 'Nenhuma descrição fornecida.' }}
                            </span>
                        </td>
                        <td>
                            <code>v{{ $theme['version'] }}</code>
                        </td>
                        <td>
                            @if($theme['is_active'])
                                <span class="admin-badge admin-badge-active">
                                    Ativo
                                </span>
                            @elseif($theme['is_installed'])
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
                            Nenhum tema disponível no momento ou falha ao carregar o catálogo.
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
    // Função para marcar/desmarcar todos os temas disponíveis
    function toggleSelectAllThemes(master) {
        const checkboxes = document.querySelectorAll('.theme-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
    }
</script>
@endpush
