@extends('admin.layout')

@section('header_title', 'Plugins')
@section('header_subtitle', 'Gerencie as extensões e módulos adicionais do sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-puzzle class="lucid-icon" /> Plugins instalados</h2>
        <a class="admin-btn admin-btn-primary" href="{{ route('admin.plugins.marketplace.index') }}">
            <x-lucide-plus class="lucid-icon" />
            Instalar plugin
        </a>
    </div>

    <!-- ABAS DE FILTRO POR TAGS / CATEGORIAS -->
    <div class="plugin-filters-bar">
        @php
            $pluginsCollection = collect($plugins);
            $totalCount = $pluginsCollection->count();
        @endphp

        <button type="button" class="plugin-filter-btn active" data-filter="all">
            <x-lucide-plug class="lucid-icon" />
            Todos <span class="filter-count">{{ $totalCount }}</span>
        </button>

        @foreach($tags as $slug => $category)
            @php
                // Conta quantos plugins possuem essa tag
                $count = $pluginsCollection->filter(function($p) use ($slug) {
                    $pTags = (array) ($p->tags ?? []);
                    return in_array($slug, $pTags);
                })->count();
            @endphp

            {{-- Só exibe a aba se houver pelo menos 1 plugin cadastrado nela --}}
            @if($count > 0)
                <button type="button" class="plugin-filter-btn" data-filter="{{ $slug }}">
                    <x-dynamic-component :component="'lucide-' . $category['icon']" class="lucid-icon" />
                    {{ $category['name'] ?? ucfirst($slug) }}
                    <span class="filter-count">{{ $count }}</span>
                </button>
            @endif
        @endforeach
    </div>

    <div class="table-wrap">
        <table class="admin-table" id="plugins-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tags</th>
                    <th>Descrição</th>
                    <th>Versão</th>
                    <th>Pasta</th>
                    <th>Status</th>
                    <th style="display: flex; justify-content: space-between;">
                        Ações
                        <div class="check-all">
                            <form method="POST" action="{{ route('admin.plugins.toggle_all', 1) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="transparent-btn status-all" title="Ativar todos os plugins">
                                    <x-lucide-check-check class="lucid-icon" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.plugins.toggle_all', 0) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="transparent-btn status-all" title="Desativar todos os plugins">
                                    <x-lucide-minus class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($plugins as $plugin)
                @php
                    $kebabName = Str::kebab($plugin->name);
                    $helpView = null;
                    if (view()->exists("{$kebabName}-help::help")) {
                        $helpView = "{$kebabName}-help::help";
                    }
                    $pluginTags = (array) ($plugin->tags ?? []);
                @endphp
                <tr
                    class="plugin-row {{ $plugin->is_active ? 'active' : 'inactive' }}"
                    data-tags="{{ implode(',', $pluginTags) }}"
                    data-plugin-name="{{ strtolower($plugin->name) }}"
                    data-folder-name="{{ strtolower($plugin->folder_name) }}"
                >
                    <td>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <strong>{{ $plugin->name }}</strong>
                            @if($helpView)
                            <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: 'help-{{ $plugin->id }}' } }))"
                                class="transparent-btn"
                                title="Mais detalhes">
                                <x-lucide-help-circle class="lucid-icon" />
                            </button>
                            <x-modal id="help-{{ $plugin->id }}" title="Ajuda: {{ $plugin->name }}" size="lg">
                                @include($helpView)
                            </x-modal>
                            @endif
                        </div>

                    </td>
                    <td>
                        {{-- BADGES DE TAGS DO PLUGIN --}}
                        @if(!empty($pluginTags))
                            <div class="plugin-tags-container">
                                @foreach($pluginTags as $t)
                                    @if(isset($tags[$t]))
                                        <span class="plugin-tag-pill" title="{{ $tags[$t]['name'] }} - {{ $tags[$t]['description'] ?? '' }}">
                                            <x-dynamic-component :component="'lucide-' . $tags[$t]['icon']" class="lucid-icon" />
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                    </td>
                    <td style="max-width: 320px; white-space: normal;">
                        <span class="admin-text-muted" style="font-size: 0.875rem;">{{ $plugin->description ?? 'Nenhuma descrição fornecida.' }}</span>
                    </td>
                    <td>
                        <code>v{{ $plugin->version }}</code>
                        @if($plugin->has_update)
                            <span class="admin-badge" style="background-color: #f59e0b; color: #ffffff; font-size: 0.7rem; margin-left: 4px;" title="Versão v{{ $plugin->remote_version }} disponível no GitHub">
                                v{{ $plugin->remote_version }} disponível!
                                @if($plugin->changelog ?? false)
                                    <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: 'changelog-{{ $plugin->id }}' } }))"
                                        class="transparent-btn"
                                        style="color: #fff"
                                        title="Mais detalhes">
                                        <x-lucide-scroll-text class="lucid-icon" />
                                    </button>
                                    <x-modal id="changelog-{{ $plugin->id }}" title="Ver mudanças" size="lg">
                                        @include('admin.plugins.changelog', ['name' => $plugin->name, 'changelog' => $plugin->changelog])
                                    </x-modal>
                                @endif
                            </span>
                        @endif
                    </td>
                    <td><code>plugins/{{ $plugin->folder_name }}</code></td>
                    <td>
                        <span class="admin-badge {{ $plugin->is_active ? 'admin-badge-active' : 'admin-badge-suspended' }}">
                            {{ $plugin->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="admin-actions" style="max-width: 140px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">

                            {{-- Switch de Ativar/Desativar --}}
                            <form method="POST" action="{{ route('admin.plugins.toggle', $plugin->id) }}" style="display: inline;">
                                @csrf
                                <x-switch name="is_active" checked="{{ old('is_active', $plugin->is_active) }}" active="Ligado" inactive="Desligado" style="top: 0;" onChange="this.form.submit()" />
                            </form>

                            {{-- Botão de Atualizar em 1 clique --}}
                            @if($plugin->has_update)
                                <form method="POST" action="{{ route('admin.plugins.marketplace.install') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_plugins[]" value="{{ json_encode(['name' => $plugin->name, 'download_url' => $plugin->download_url]) }}">
                                    <button type="submit" class="admin-btn admin-btn-sm" style="background-color: #f59e0b; color: #ffffff; border: none; padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Atualizar para v{{ $plugin->remote_version }}">
                                        <x-lucide-refresh-cw class="lucid-icon" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="admin-empty-list">
                            <div>
                                <x-lucide-circle-off class="lucid-icon" />
                            </div>
                            <h3>Nenhum plugin encontrado</h3>
                            <p>
                                Insira novas pastas de plugins no diretório
                                <code>/plugins</code> na raiz do projeto.
                                Ou busque um no <a href="{{ route('admin.plugins.marketplace.index') }}">repositório</a>.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse

                <!-- Mensagem dinâmica caso o filtro não encontre resultados -->
                <tr id="no-filter-match" style="display: none;">
                    <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                        Nenhum plugin encontrado nesta categoria.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/pages/plugin-help.css') }}">
<style>
    /* BARRA DE FILTRO POR ABAS */
    .plugin-filters-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-bottom: 1px solid var(--color-border, #e2e8f0);
        /* overflow-x: auto; */
        flex-wrap: wrap;
        white-space: nowrap;
        background: #f8fafc;
    }

    .plugin-filter-btn {
        background: #ffffff;
        border: 1px solid var(--color-border, #cbd5e1);
        color: #475569;
        font-size: 0.815rem;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }

    .plugin-filter-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    .plugin-filter-btn.active {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .filter-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .plugin-filter-btn.active .filter-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* BADGES DE TAGS DENTRO DA LINHA DO PLUGIN */
    .plugin-tags-container {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .plugin-tag-pill {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 3px;
        border-radius: 4px;
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        border-radius: 50%;
        letter-spacing: 0.2px;
    }

    .plugin-tag-pill svg {
        width: 14px;
        height: 14px;
    }

    .status-all {
        width: 24px;
        height: 24px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        margin: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .status-all:hover {
        background-color: #3b82f6;
        color: #ffffff;
    }
    tr.inactive {
        background-color: #fafafa;
    }
    /* DESTAQUE COM FADE-OUT AO BUSCAR PLUGIN */
    .row-highlight td {
        animation: highlightFade 20s ease-out forwards;
    }

    @keyframes highlightFade {
        0% {
            background-color: #fef08a; /* Amarelo bem visível */
        }
        70% {
            background-color: #fef9c3;
        }
        100% {
            background-color: transparent;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.plugin-filter-btn');
    const rows = document.querySelectorAll('.plugin-row');
    const noMatchRow = document.getElementById('no-filter-match');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Alterna a classe ativa
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');
            let visibleCount = 0;

            rows.forEach(row => {
                const tags = row.getAttribute('data-tags') ? row.getAttribute('data-tags').split(',') : [];

                if (filter === 'all' || tags.includes(filter)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noMatchRow) {
                noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
        });
    });

    // =========================================================================
    // SCROLL + DESTAQUE COM FADE SE HOUVER ?search= OU ?highlight= NA URL
    // =========================================================================
    const urlParams = new URLSearchParams(window.location.search);
    const searchTarget = (urlParams.get('search') || urlParams.get('highlight') || '').toLowerCase().trim();

    if (searchTarget) {
        // Procura a linha que bate com o nome do plugin ou nome da pasta
        const targetRow = Array.from(rows).find(row => {
            const pName = row.getAttribute('data-plugin-name') || '';
            const fName = row.getAttribute('data-folder-name') || '';
            return pName === searchTarget || fName === searchTarget || pName.includes(searchTarget);
        });

        if (targetRow) {
            // Garante que a linha esteja visível mesmo se tiver filtro ativo
            targetRow.style.display = '';

            // Rola suavemente até o elemento centralizando na tela
            setTimeout(() => {
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetRow.classList.add('row-highlight');

                // Remove a classe após a animação terminar
                setTimeout(() => {
                    targetRow.classList.remove('row-highlight');
                }, 20000);
            }, 150);
        }
    }
});
</script>
@endpush
