@extends('admin.layout')

@section('header_title', 'Adicionar ou Remover Temas')
@section('header_subtitle', 'Explore, baixe e instale novos temas para o Lunar Base')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-palette class="lucid-icon" /> Temas Disponíveis</h2>
        <div class="top-buttons">

            {{-- Botão Voltar para Temas Instalados --}}
            <a href="{{ route('admin.themes.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" />
                Voltar
            </a>

            {{-- Formulário de Recarregar Catálogo --}}
            <form method="POST" action="{{ route('admin.themes.marketplace.refresh') }}" style="display: inline;">
                @csrf
                <button type="submit" class="admin-btn admin-btn-secondary" title="Atualizar catálogo remoto">
                    <x-lucide-refresh-cw class="lucid-icon" />
                    Atualizar
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
                                    Instalado
                                </span>
                                <a href="{{ route('admin.themes.marketplace.remove', $theme['folder']) }}" data-name="{{ $theme['name'] }}" class="remove-theme" title="Remover tema">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </a>
                            @else
                                <span class="admin-badge" style="background-color: #e5e7eb; color: #4b5563;">
                                    Não Instalado
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="admin-empty-list admin-text-muted" style="padding: 2rem;">
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

@push('styles')
<style>
a.remove-theme {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-danger, #ef4444);
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.25);
    padding: 0.2rem 0.55rem;
    border-radius: 4px;
    text-decoration: none;
    margin-left: 0.5rem;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    line-height: 1.2;
}

a.remove-theme:hover {
    background-color: var(--color-danger, #ef4444);
    color: #ffffff;
    border-color: var(--color-danger, #ef4444);
}

a.remove-theme .lucid-icon {
    width: 13px;
    height: 13px;
}
</style>
@endpush

@push('scripts')
<script>
    // Função para marcar/desmarcar todos os temas disponíveis
    function toggleSelectAllThemes(master) {
        const checkboxes = document.querySelectorAll('.theme-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
    }

    // Função de remoção de theme
    async function removeTheme(event) {
        event.preventDefault();

        // Garante que pega o elemento <a> mesmo se clicar em um ícone interno
        const link = event.target.closest('a.remove-theme');
        if (!link) return;

        const name = link.dataset.name;
        const url = link.href;

        // Confirmação via seu componente Dialog
        const confirmed = await Dialog.confirm(`Deseja remover o tema ${name}?`);
        if (!confirmed) return;

        // Cria um formulário dinâmico para submeter via POST/DELETE com CSRF
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        // Token CSRF
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Spoofing de método DELETE do Laravel
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Anexa ao DOM e envia
        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('click', event => {
        if (event.target.matches('a.remove-theme') || event.target.closest('a.remove-theme')) {
            removeTheme(event);
        }
    });
</script>
@endpush
