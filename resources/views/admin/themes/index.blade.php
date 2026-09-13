@extends('admin.layout')

@section('header_title', 'Aparência')
@section('header_subtitle', 'Personalize o visual e os templates do seu site público')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-palette class="lucid-icon" /> Temas Instalados</h2>
        <a class="admin-btn admin-btn-primary" href="{{ route('admin.themes.marketplace.index') }}">
            <x-lucide-plus class="lucid-icon" />
            Instalar tema
        </a>
    </div>

    <p style="padding: 0 24px; margin-top: 12px; color: var(--color-text-muted, #6B6880); font-size: 0.9rem;">
        O sistema não precisa de um tema instalado, o site já tem seu próprio frontend. Um tema pode sobrescrever a formatação ao criar views para partes do site ou para o site inteiro.
    </p>

    <!-- ABAS DE FILTRO POR CATEGORIAS/TAGS -->
    <div class="theme-filters-bar">
        @php
            $themesCollection = collect($themes);
            $totalCount = $themesCollection->count();
        @endphp

        <button type="button" class="theme-filter-btn active" data-filter="all">
            Todos <span class="filter-count">{{ $totalCount }}</span>
        </button>

        @foreach($tags as $slug => $category)
            @php
                $count = $themesCollection->filter(function($t) use ($slug) {
                    return in_array($slug, (array) ($t->tags ?? []));
                })->count();
            @endphp

            @if($count > 0)
                <button type="button" class="theme-filter-btn" data-filter="{{ $slug }}">
                    @if(!empty($category['icon']))
                        <x-dynamic-component :component="'lucide-' . $category['icon']" class="lucid-icon-sm" />
                    @endif
                    {{ $category['name'] ?? ucfirst($slug) }}
                    <span class="filter-count">{{ $count }}</span>
                </button>
            @endif
        @endforeach
    </div>

    <div class="theme-grid">
        @forelse($themes as $theme)
            @php
                $themeTags = (array) ($theme->tags ?? []);
            @endphp
            <div
                class="theme-card {{ $theme->is_active ? 'theme-active' : '' }}"
                data-tags="{{ implode(',', $themeTags) }}"
            >

                <!-- Theme Preview Area: Exibe imagem se existir, ou fallback textual elegante -->
                @if($theme->screenshot)
                    <div class="theme-preview-image">
                        <img src="{{ route('admin.themes.screenshot', $theme->id) }}" alt="{{ $theme->name }}">
                    </div>
                @else
                    <div class="theme-preview-placeholder">
                        <span>{{ strtoupper($theme->name) }}</span>
                    </div>
                @endif

                <div class="theme-details">
                    <div class="theme-header-row">
                        <div class="theme-title-area">
                            <h3 class="theme-name">{{ $theme->name }}</h3>

                            {{-- ÍCONES CIRCULARES DE TAGS COM TOOLTIP --}}
                            @if(!empty($themeTags))
                                <div class="theme-tags-container">
                                    @foreach($themeTags as $t)
                                        @if(isset($tags[$t]))
                                            <span
                                                class="theme-tag-pill"
                                                title="{{ $tags[$t]['name'] }}{{ !empty($tags[$t]['description']) ? ' — ' . $tags[$t]['description'] : '' }}"
                                            >
                                                @if(!empty($tags[$t]['icon']))
                                                    <x-dynamic-component :component="'lucide-' . $tags[$t]['icon']" class="lucid-icon-xs" />
                                                @else
                                                    {{ substr($tags[$t]['name'], 0, 2) }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <span class="admin-badge {{ $theme->is_active ? 'admin-badge-active' : 'admin-badge-suspended' }}">
                            {{ $theme->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <p class="theme-description">
                        {{ $theme->description ?? 'Nenhuma descrição fornecida para este tema.' }}
                    </p>

                    <div class="theme-meta">
                        <span>
                            <strong>Versão:</strong> v{{ $theme->version }}
                            @if($theme->has_update)
                                <span class="admin-badge" style="background-color: #f59e0b; color: #ffffff; font-size: 0.7rem; margin-left: 4px;" title="Versão v{{ $theme->remote_version }} disponível no GitHub">
                                    v{{ $theme->remote_version }} disponível!
                                </span>
                            @endif
                        </span>
                        <span><strong>Autor:</strong> {{ $theme->author }}</span>
                        <span><strong>Pasta:</strong> themes/{{ $theme->folder_name }}</span>
                    </div>

                    <div class="theme-footer" style="display: flex; justify-content: space-between; align-items: center;">
                        @if(!$theme->is_active)
                        <div>
                            <button type="button"
                                    onclick="openThemePreview('{{ $theme->folder_name }}')"
                                    class="admin-btn admin-btn-secondary"
                                    title="Preview">
                                <x-lucide-eye class="lucid-icon" />
                            </button>
                        </div>
                        @endif

                        <div>
                            @if($theme->has_update)
                                <form method="POST" action="{{ route('admin.themes.marketplace.install') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_themes[]" value="{{ json_encode(['name' => $theme->name, 'download_url' => $theme->download_url]) }}">
                                    <button type="submit" class="admin-btn admin-btn-sm" style="background-color: #f59e0b; color: #ffffff; border: none; padding: 0.25rem 0.55rem; font-size: 0.75rem;" title="Atualizar para v{{ $theme->remote_version }}">
                                        <x-lucide-refresh-cw class="lucid-icon" /> Atualizar
                                    </button>
                                </form>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.themes.toggle', $theme->id) }}">
                            @csrf
                            <x-switch
                                name="is_active"
                                id="theme_{{ $theme->id }}"
                                :checked="$theme->is_active"
                                active="Ativo"
                                inactive="Inativo"
                                onChange="this.form.submit()"
                            />
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="admin-empty-list" style="grid-column: 1 / -1;">
                <div>
                    <x-lucide-circle-off class="lucid-icon" />
                </div>
                <h3>Nenhum tema encontrado</h3>
                <p>
                    Insira novas pastas de temas no diretório
                    <code>/themes</code> na raiz do projeto.
                    Ou busque um no <a href="{{ route('admin.themes.marketplace.index') }}">repositório</a>.
                </p>
            </div>
        @endforelse

        <!-- Mensagem se o filtro não encontrar nenhum tema correspondente -->
        <div id="no-filter-match-theme" class="admin-empty-list" style="display: none; grid-column: 1 / -1; padding: 2rem;">
            <p style="color: #64748b;">Nenhum tema encontrado nesta categoria.</p>
        </div>
    </div>
</div>

<!-- Modal de Tela Cheia com iframe -->
<div id="preview-modal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.8);">
    <div style="background: #1e293b; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
        <span id="preview-title" style="font-weight: 600;">Modo de Visualização</span>
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="closeThemePreview()" class="admin-btn admin-btn-secondary" style="background: #ef4444; border: none; color: white;">
                <x-lucide-x class="lucid-icon" /> Fechar Visualização
            </button>
        </div>
    </div>
    <iframe id="preview-iframe" src="" style="width: 100%; height: calc(100vh - 52px); border: none; background: white;"></iframe>
</div>

@once
@push('scripts')
<script>
// Filtro interativo instantâneo por categorias
document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.theme-filter-btn');
    const cards = document.querySelectorAll('.theme-card');
    const noMatchMessage = document.getElementById('no-filter-match-theme');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');
            let visibleCount = 0;

            cards.forEach(card => {
                const tags = card.getAttribute('data-tags') ? card.getAttribute('data-tags').split(',') : [];

                if (filter === 'all' || tags.includes(filter)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noMatchMessage) {
                noMatchMessage.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
            }
        });
    });
});

function openThemePreview(themeFolder) {
    const modal = document.getElementById('preview-modal');
    const iframe = document.getElementById('preview-iframe');
    const title = document.getElementById('preview-title');

    title.innerText = `Visualizando Tema: ${themeFolder}`;
    iframe.src = `/?preview_theme=${themeFolder}`;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeThemePreview() {
    const modal = document.getElementById('preview-modal');
    const iframe = document.getElementById('preview-iframe');

    fetch('{{ route("admin.themes.clear_preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).finally(() => {
        iframe.src = 'about:blank';
        modal.style.display = 'none';
        document.body.style.overflow = '';
    });
}
</script>
@endpush

@push('styles')
<style>
    /* BARRA DE FILTRO POR ABAS */
    .theme-filters-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-bottom: 1px solid var(--color-border, #e2e8f0);
        overflow-x: auto;
        white-space: nowrap;
        background: #f8fafc;
    }

    .theme-filter-btn {
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

    .theme-filter-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    .theme-filter-btn.active {
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

    .theme-filter-btn.active .filter-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    .lucid-icon-sm {
        width: 14px;
        height: 14px;
    }

    /* TAGS CIRCULARES NO CARD */
    .theme-title-area {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .theme-tags-container {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .theme-tag-pill {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        cursor: help;
        transition: transform 0.15s ease;
    }

    .theme-tag-pill:hover {
        transform: scale(1.15);
    }

    .lucid-icon-xs {
        width: 12px;
        height: 12px;
    }

    /* --- Estilização da Galeria de Temas (Lunar Base UI) --- */
    .theme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        padding: 24px;
    }

    .theme-card {
        background-color: var(--color-bg-card, #ffffff);
        border: 1px solid var(--color-border, #e5e7eb);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: border-color var(--transition-fast, 0.2s ease), box-shadow var(--transition-fast, 0.2s ease);
    }

    .theme-card:hover {
        border-color: var(--color-border-hover, #cbd5e1);
    }

    .theme-card.theme-active {
        border-color: var(--color-primary-dark, #5E3FAE);
        box-shadow: 0 0 15px var(--color-glow, rgba(123, 95, 199, 0.08));
    }

    .theme-preview-image {
        height: 160px;
        overflow: hidden;
        border-bottom: 1px solid var(--color-border, #e5e7eb);
        background-color: var(--color-bg-dark, #F5F3FA);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .theme-preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .theme-preview-placeholder {
        background: linear-gradient(135deg, var(--color-bg-dark, #F5F3FA), var(--color-bg-card-hover, #F0EDF8));
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid var(--color-border, #e5e7eb);
    }

    .theme-preview-placeholder span {
        font-family: var(--font-heading, serif);
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 2px;
        color: var(--color-primary, #7B5FC7);
    }

    .theme-details {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .theme-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 0.75rem;
    }

    .theme-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text, #2D2A3E);
        margin: 0;
    }

    .theme-description {
        font-size: 0.875rem;
        color: var(--color-text-muted, #6B6880);
        line-height: 1.5;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .theme-meta {
        font-size: 0.75rem;
        color: var(--color-text-dim, #9A97B0);
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        margin-bottom: 1.25rem;
        padding-top: 0.75rem;
        border-top: 1px dashed var(--color-border, #e5e7eb);
    }

    .theme-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        height: 38px;
    }

    .switch-label {
        position: static !important;
    }
</style>
@endpush
@endonce
@endsection
