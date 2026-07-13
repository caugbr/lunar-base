{{-- admin/dashboard/boxes/stats.blade.php --}}

<div class="stats-box">
    {{-- Cards Principais --}}
    <div class="stats-grid">
        {{-- Páginas Publicadas --}}
        <div class="stat-card stat-pages">
            <div class="stat-icon">
                <x-lucide-file class="lucid-icon" />
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $pages }}</span>
                <span class="stat-label">Páginas</span>
            </div>
        </div>

        {{-- Posts Publicados --}}
        <div class="stat-card stat-posts">
            <div class="stat-icon">
                <x-lucide-files class="lucid-icon" />
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $posts }}</span>
                <span class="stat-label">Posts</span>
            </div>
        </div>

        {{-- Plugins Ativos --}}
        <div class="stat-card stat-plugins">
            <div class="stat-icon">
                <x-lucide-puzzle class="lucid-icon" />
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $activePlugins }}</span>
                <span class="stat-label">Plugins Ativos</span>
            </div>
        </div>
    </div>

        {{-- Tema Ativo --}}
        <div class="stat-card stat-theme">
            <div class="stat-icon">
                <x-lucide-palette class="lucid-icon" />
            </div>
            <div class="stat-content">
                <span class="stat-label">Tema Ativo</span>
                <span class="stat-value stat-value-text">{{ $activeTheme ?? 'Nenhum' }}</span>
            </div>
        </div>

    {{-- Distribuição de Usuários --}}
    <div class="stats-users-section">
        <h4 class="stats-section-title">
            <x-lucide-users class="lucid-icon" />
            Usuários por Perfil
        </h4>

        <div class="stats-users-list">
            @foreach($users as $role => $data)
            <div class="stats-user-item">
                <div class="user-role-info">
                    <span class="user-role-name">{{ $data['name'] }}</span>
                    {{-- <span class="user-role-slug">{{ $role }}</span> --}}
                </div>
                <div class="user-role-count">
                    <span class="count-number">{{ $data['count'] }}</span>
                    <span class="count-label">{{ Str::plural('usuário', $data['count']) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.stats-box {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Grid de Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--color-bg-dark, #f3f4f6);
    border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    border-radius: 8px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    border-radius: 8px;
    flex-shrink: 0;
}

.stat-icon .lucid-icon {
    width: 20px;
    height: 20px;
    color: var(--color-text, #333333);
}

.stat-content {
    display: flex;
    /* flex-direction: column; */
    align-items: baseline;
    gap: 0.25rem;
    flex: 1;
    min-width: 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-text, #333333);
    line-height: 1;
}

.stat-value-text {
    font-size: 1.1rem;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--color-text-muted, #6b7280);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Seção de Usuários */
.stats-users-section {
    background: var(--color-bg-dark, #f3f4f6);
    border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    border-radius: 8px;
    padding: 1.25rem;
}

.stats-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--color-text, #333333);
    margin: 0 0 1rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
}

.stats-section-title .lucid-icon {
    width: 18px;
    height: 18px;
    color: var(--color-primary, #667eea);
}

.stats-users-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stats-user-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: var(--color-bg-card, #ffffff);
    border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    border-radius: 6px;
    transition: background 0.2s ease;
}

.stats-user-item:hover {
    background: var(--color-bg-card-hover, #f9fafb);
}

.user-role-info {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    flex: 1;
    min-width: 0;
}

.user-role-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--color-text, #333333);
}

.user-role-slug {
    font-size: 0.75rem;
    color: var(--color-text-muted, #6b7280);
    font-family: monospace;
}

.user-role-count {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
}

.count-number {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-primary, #667eea);
    line-height: 1;
}

.count-label {
    font-size: 0.7rem;
    color: var(--color-text-muted, #6b7280);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Responsivo */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
    }

    .stat-icon .lucid-icon {
        width: 20px;
        height: 20px;
    }

    .stat-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stats-user-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .user-role-count {
        align-items: flex-start;
    }
}
</style>
