{{-- plugins/Core/resources/views/dashboard/boxes/welcome.blade.php --}}

<div class="welcome-box">
    {{-- Saudação personalizada --}}
    <div class="welcome-greeting">
        @php
            $hour = (int) date('H');
            $greeting = match(true) {
                $hour >= 5 && $hour < 12  => 'Bom dia',
                $hour >= 12 && $hour < 18 => 'Boa tarde',
                default                   => 'Boa noite',
            };
            $userName = auth()->user()?->name ?? 'Visitante';
        @endphp

        <h3 class="welcome-title">
            {{-- <x-lucide-sparkles class="lucid-icon" style="color: var(--color-primary);" /> --}}
            {{ $greeting }}, {{ $userName }}!
        </h3>

        <p class="welcome-text">
            Bem-vindo ao painel de controle do <strong>{{ config('app.name') }}</strong>.<br>
            Aqui você gerencia todo o conteúdo do seu site de forma simples e eficiente.
        </p>
    </div>

    {{-- Informações do sistema --}}
    <div class="welcome-info">
        <div class="info-item">
            <x-lucide-code-2 class="lucid-icon" />
            <div>
                <span class="info-label">Sistema</span>
                <span class="info-value">{{ config('app.name') }}</span>
            </div>
        </div>

        <div class="info-item">
            <x-lucide-tag class="lucid-icon" />
            <div>
                <span class="info-label">Versão</span>
                <span class="info-value">{{ config('app.version') }}</span>
            </div>
        </div>

        <div class="info-item">
            <x-lucide-user class="lucid-icon" />
            <div>
                <span class="info-label">Desenvolvido por</span>
                <span class="info-value">{{ config('app.author') }}</span>
            </div>
        </div>

        {{-- <div class="info-item">
            <x-lucide-calendar class="lucid-icon" />
            <div>
                <span class="info-label">Data atual</span>
                <span class="info-value">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div> --}}
    </div>

    {{-- Links rápidos --}}
    <div class="welcome-actions">
        <a href="{{ route('admin.settings.index') }}" class="welcome-action-btn">
            <x-lucide-settings class="lucid-icon" />
            <span>Configurações</span>
        </a>

        <a href="{{ route('admin.plugins.index') }}" class="welcome-action-btn">
            <x-lucide-puzzle class="lucid-icon" />
            <span>Plugins</span>
        </a>

        <a href="{{ route('admin.themes.index') }}" class="welcome-action-btn">
            <x-lucide-palette class="lucid-icon" />
            <span>Temas</span>
        </a>

        <a href="/tutorials/index.html" class="welcome-action-btn" target="_blank">
            <x-lucide-external-link class="lucid-icon" />
            <span>Tutoriais</span>
        </a>

        {{-- <a href="{{ route('admin.pages.index') }}" class="welcome-action-btn">
            <x-lucide-file class="lucid-icon" />
            <span>Páginas</span>
        </a> --}}

        {{-- <a href="{{ route('admin.posts.index') }}" class="welcome-action-btn">
            <x-lucide-files class="lucid-icon" />
            <span>Posts</span>
        </a> --}}

        {{-- <a href="{{ route('admin.media.index') }}" class="welcome-action-btn">
            <x-lucide-image class="lucid-icon" />
            <span>Mídia</span>
        </a> --}}

    </div>

    {{-- Dica do dia
    @php
        $tips = [
            'Use o construtor de menus para criar navegações dinâmicas que se atualizam automaticamente.',
            'Formulários podem ser incorporados em qualquer página usando shortcodes.',
            'O modo de manutenção preserva seu SEO retornando status 503 para robôs de busca.',
            'Mapas interativos suportam marcadores personalizados e áreas destacadas via GeoJSON.',
            'Comentários possuem moderação automática de spam baseada em palavras-chave.',
            'FAQs são exibidos em acordeões nativos HTML5, funcionando sem JavaScript.',
        ];
        $tip = $tips[array_rand($tips)];
    @endphp

    <div class="welcome-tip">
        <x-lucide-lightbulb class="lucid-icon" />
        <div>
            <strong>Dica:</strong> {{ $tip }}
        </div>
    </div> --}}
</div>

<style>
.welcome-box {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.welcome-greeting {
    text-align: center;
    padding: 1rem 0;
}

.welcome-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-text);
    margin: 0 0 0.75rem 0;
}

.welcome-title .lucid-icon {
    width: 24px;
    height: 24px;
}

.welcome-text {
    color: var(--color-text-muted);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

.welcome-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
    background: var(--color-bg-dark);
    border-radius: 8px;
    border: 1px solid var(--color-border);
}

.info-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.info-item .lucid-icon {
    width: 20px;
    height: 20px;
    color: var(--color-primary);
    flex-shrink: 0;
}

.info-item > div {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.info-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-text-muted);
    font-weight: 600;
}

.info-value {
    font-size: 0.9rem;
    color: var(--color-text);
    font-weight: 500;
}

.welcome-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
}

.welcome-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    color: var(--color-text);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.welcome-action-btn:hover {
    background: var(--color-bg-card-hover, var(--color-bg-dark));
    border-color: var(--color-primary);
    transform: translateY(-2px);
}

.welcome-action-btn .lucid-icon {
    width: 18px;
    height: 18px;
    color: var(--color-primary);
}

.welcome-tip {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    font-size: 0.875rem;
    line-height: 1.6;
    color: var(--color-text);
}

.welcome-tip .lucid-icon {
    width: 20px;
    height: 20px;
    color: var(--color-primary);
    flex-shrink: 0;
    margin-top: 2px;
}

.welcome-tip strong {
    color: var(--color-primary);
}

@media (max-width: 640px) {
    .welcome-info {
        grid-template-columns: 1fr;
    }

    .welcome-actions {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
