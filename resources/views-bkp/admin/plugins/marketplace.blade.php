@extends('admin.layout')

@section('header_title', 'Adicionar ou Remover Plugins')
@section('header_subtitle', 'Explore, baixe e instale novas extensões para o Lunar Base')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-puzzle class="lucid-icon" /> Plugins Disponíveis</h2>
        <div class="top-buttons">

            {{-- Botão Voltar para Lista Local --}}
            <a href="{{ route('admin.plugins.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" />
                Voltar
            </a>

            {{-- Formulário de Recarregar Catálogo --}}
            <form method="POST" action="{{ route('admin.plugins.marketplace.refresh') }}" style="display: inline;">
                @csrf
                <button type="submit" class="admin-btn admin-btn-secondary" title="Atualizar catálogo remoto">
                    <x-lucide-refresh-cw class="lucid-icon" />
                    Atualizar
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
                    {{-- @php print_r($plugin); @endphp --}}
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
                            @php
                                $version = $plugin['is_installed']
                                            ? $plugin['local_version']
                                            : $plugin['remote_version'];
                            @endphp
                            <code>v{{ $version }}</code>
                        </td>
                        <td>
                            @if($plugin['is_active'])
                                <span class="admin-badge admin-badge-active">
                                    Ativo
                                </span>
                            @elseif($plugin['is_installed'])
                                <span class="admin-badge admin-badge-suspended">
                                    Instalado
                                </span>
                                <a href="{{ route('admin.plugins.marketplace.remove', $plugin['folder']) }}" data-name="{{ $plugin['name'] }}" class="remove-plugin" title="Remover plugin">
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

@push('styles')
<style>
a.remove-plugin {
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

a.remove-plugin:hover {
    background-color: var(--color-danger, #ef4444);
    color: #ffffff;
    border-color: var(--color-danger, #ef4444);
}

a.remove-plugin .lucid-icon {
    width: 13px;
    height: 13px;
}
</style>
@endpush

@push('scripts')
<script>
    // Função para marcar/desmarcar todos os checkboxes
    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.plugin-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
    }

    // Função de remoção de plugin
    async function removePlugin(event) {
        event.preventDefault();

        // Garante que pega o elemento <a> mesmo se clicar em um ícone interno
        const link = event.target.closest('a.remove-plugin');
        if (!link) return;

        const name = link.dataset.name;
        const url = link.href;

        // Confirmação via seu componente Dialog
        const confirmed = await Dialog.confirm(`Deseja remover o plugin ${name}?`);
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
        if (event.target.matches('a.remove-plugin') || event.target.closest('a.remove-plugin')) {
            removePlugin(event);
        }
    });
</script>
@endpush
