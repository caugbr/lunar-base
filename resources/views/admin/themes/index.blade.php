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

    <p>
        O sistema não precisa de um tema instalado, o site já tem seu próprio frontend. Um tema pode sobrescrever a formatação ao criar views para partes do site ou para o site inteiro.
    </p>

    <div class="theme-grid">
        @forelse($themes as $theme)
            <div class="theme-card {{ $theme->is_active ? 'theme-active' : '' }}">

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
                        <h3 class="theme-name">{{ $theme->name }}</h3>
                        <span class="admin-badge {{ $theme->is_active ? 'admin-badge-active' : 'admin-badge-suspended' }}">
                            {{ $theme->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <p class="theme-description">
                        {{ $theme->description ?? 'Nenhuma descrição fornecida para este tema.' }}
                    </p>

                    {{-- <div class="theme-meta">
                        <span><strong>Versão:</strong> {{ $theme->version }}</span>
                        <span><strong>Autor:</strong> {{ $theme->author }}</span>
                        <span><strong>Pasta:</strong> themes/{{ $theme->folder_name }}</span>
                    </div> --}}
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


                    {{-- <div class="theme-footer">
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
                    </div> --}}
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
            <div class="admin-empty-list">
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
    </div>
</div>

<!-- Modal de Tela Cheia com iframe -->
<div id="preview-modal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.8);">
    <!-- Barra superior do Preview -->
    <div style="background: #1e293b; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
        <span id="preview-title" style="font-weight: 600;">Modo de Visualização</span>
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="closeThemePreview()" class="admin-btn admin-btn-secondary" style="background: #ef4444; border: none; color: white;">
                <x-lucide-x class="lucid-icon" /> Fechar Visualização
            </button>
        </div>
    </div>

    <!-- Iframe que carrega o site -->
    <iframe id="preview-iframe" src="" style="width: 100%; height: calc(100vh - 52px); border: none; background: white;"></iframe>
</div>

@once
@push('scripts')
<script>
function openThemePreview(themeFolder) {
    const modal = document.getElementById('preview-modal');
    const iframe = document.getElementById('preview-iframe');
    const title = document.getElementById('preview-title');

    title.innerText = `Visualizando Tema: ${themeFolder}`;

    // Abre o iframe passando o tema via GET (que seta a sessão)
    iframe.src = `/?preview_theme=${themeFolder}`;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeThemePreview() {
    const modal = document.getElementById('preview-modal');
    const iframe = document.getElementById('preview-iframe');

    // 1. Limpa a sessão no backend via AJAX
    fetch('{{ route("admin.themes.clear_preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).finally(() => {
        // 2. Limpa o iframe e fecha o modal
        iframe.src = 'about:blank';
        modal.style.display = 'none';
        document.body.style.overflow = '';
    });
}
</script>
@endpush
@push('styles')
<style>
    /* --- Estilização da Galeria de Temas (Lunar Base UI) --- */
    .theme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        padding: 24px;
    }

    /* Alterado para priorizar fundo mais claro e melhor contraste */
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

    /* CSS para Exibição de Screenshot */
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

    /* CSS Placeholder para o Fallback de Preview do Tema */
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
        align-items: center;
        justify-content: space-between;
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

    .theme-active-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--color-primary-dark, #5E3FAE);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .theme-active-text .lucid-icon {
        width: 16px;
        height: 16px;
    }

    .switch-label {
        position: static !important;
    }
</style>
@endpush
@endonce
@endsection
