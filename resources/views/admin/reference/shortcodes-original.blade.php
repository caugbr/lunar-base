@extends('admin.layout')
@section('header_title', 'Shortcodes do Sistema')
@section('header_subtitle', 'Códigos de atalho disponíveis para uso no editor de conteúdo')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/hooks.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-brackets class="lucid-icon" /> Referência de Shortcodes</h2>
    </div>

    <div class="shortcodes-explanation">
        <h3><x-lucide-info class="lucid-icon" /> O que são Shortcodes?</h3>
        <p>
            Shortcodes (ou Códigos de Atalho) são marcadores especiais delimitados por colchetes que você pode inserir diretamente no editor de texto de posts e páginas. Quando o conteúdo é renderizado no site público, o motor do sistema intercepta esses marcadores e os substitui de forma dinâmica por componentes visuais complexos, reprodutores de mídia responsivos ou marcações HTML seguras.
        </p>

        <h4>Como Usar</h4>
        <div class="shortcodes-usage-grid">
            <div class="usage-card">
                <h5><x-lucide-square-code class="lucid-icon" /> Tag Auto-fechada</h5>
                <p>Usada quando o shortcode carrega todas as informações necessárias através de seus atributos:</p>
                <code>[link rel="stylesheet" href="..."]</code>
            </div>
            <div class="usage-card">
                <h5><x-lucide-code-2 class="lucid-icon" /> Tag de Fechamento</h5>
                <p>Usada quando o shortcode precisa processar um bloco de conteúdo ou texto inserido entre a abertura e o fechamento:</p>
                <code>[style] .minha-classe { color: red; } [/style]</code>
            </div>
        </div>
    </div>

    <div class="filter">
        Mostrar:
        <label><input type="radio" name="show" value="all" checked> Todos</label>
        <label><input type="radio" name="show" value="core"> Sistema / Core</label>
        <label><input type="radio" name="show" value="plugin"> Plugins ativos</label>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-tag">Tag</th>
                    <th class="col-type">Origem</th>
                    <th class="col-example">Exemplo de Uso</th>
                    <th class="col-desc">Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shortcodes as $tag => $data)
                <tr data-type="{{ strtolower($data['type']) }}">
                    <td>
                        <code class="shortcode-tag">[{{ $tag }}]</code>
                    </td>
                    <td>
                        @if($data['type'] === 'Core')
                            <span class="admin-badge admin-badge-info">
                                <x-lucide-cpu class="lucid-icon badge-icon" /> Core
                            </span>
                        @else
                            <span class="admin-badge admin-badge-active">
                                <x-lucide-plug class="lucid-icon badge-icon" /> Plugin
                            </span>
                        @endif
                    </td>
                    <td class="shortcode-example">
                        <code class="example-code">{{ $data['example'] }}</code>
                    </td>
                    <td class="shortcode-description">
                        {{ $data['description'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-text-center admin-text-muted empty-row-padding">
                        Nenhum shortcode ativo foi detectado no sistema no momento.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@once
@push('styles')
<style>
    /* Estilos do filtro unificado */
    .filter {
        background: var(--color-bg-dark);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    /* Grid explicativo de uso dos shortcodes */
    .shortcodes-explanation {
        background: rgba(0, 0, 0, 0.02);
        border: 1px dashed var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .shortcodes-explanation h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.125rem;
    }
    .shortcodes-usage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .usage-card {
        background: var(--color-bg-light, #ffffff);
        border: 1px solid var(--color-border);
        border-radius: 6px;
        padding: 1rem;
    }
    .usage-card h5 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .usage-card p {
        margin: 0 0 0.75rem 0;
        font-size: 0.85rem;
        color: var(--color-text-muted);
        line-height: 1.4;
    }
    .usage-card code {
        display: block;
        background: #f1f5f9;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #0f172a;
    }

    /* Definição de larguras das colunas para evitar inline-styles */
    .col-tag { width: 20%; }
    .col-type { width: 15%; }
    .col-example { width: 35%; }
    .col-desc { width: auto; }

    /* Estilização da tabela de shortcodes */
    .table-wrap {
        margin-top: 1.5rem;
    }
    .shortcode-tag {
        font-weight: 600;
        color: #2563eb;
    }
    .example-code {
        font-size: 0.8rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        display: block;
        word-break: break-all;
    }
    .badge-icon {
        width: 12px;
        height: 12px;
        margin-right: 2px;
    }
    .empty-row-padding {
        padding: 30px;
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
            const selectedFilter = this.value; // 'all', 'core' ou 'plugin'

            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');

                // Se a linha não tiver o atributo (ex: a linha de "Nenhum shortcode ativo"), ignora
                if (!rowType) return;

                // Lógica de visibilidade baseada no atributo data-type
                if (selectedFilter === 'all') {
                    row.style.display = '';
                } else if (selectedFilter === 'core') {
                    row.style.display = (rowType === 'core') ? '' : 'none';
                } else if (selectedFilter === 'plugin') {
                    row.style.display = (rowType === 'plugin') ? '' : 'none';
                }
            });
        });
    });
});
</script>
@endpush
@endonce
