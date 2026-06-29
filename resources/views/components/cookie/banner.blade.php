@php
    // Busca dinamicamente a página de privacidade salva no seu banco de dados para linkar no banner
    $privacyPage = \App\Models\Page::published()->where('slug', 'politica-de-privacidade')->first();
    $link = $privacyPage ? $privacyPage->url : '#';
@endphp

<!-- Banner de Cookies Principal -->
<div id="lgpd-cookie-banner" class="cookie-banner" style="display: none;">
    <div class="cookie-content">
        <p>
            Nós usamos cookies para melhorar sua experiência. Ao continuar navegando, você concorda com a nossa
            <a href="{{ $link }}">Política de Privacidade</a>.
        </p>
        <div class="cookie-actions">
            <button id="cookie-customize-btn" class="cookie-btn-link">Personalizar</button>
            <button id="cookie-accept-all-btn" class="cookie-btn">Aceitar Todos</button>
        </div>
    </div>
</div>

<!-- Modal de Personalização Detalhado (Cookies e Transparência) -->
<div id="lgpd-cookie-modal" class="cookie-modal-overlay" style="display: none;">
    <div class="cookie-modal-box">
        <div class="cookie-modal-header">
            <h3>Definições de Privacidade</h3>
            <button id="cookie-modal-close-btn" class="cookie-modal-close" aria-label="Fechar">&times;</button>
        </div>

        <div class="cookie-modal-body">
            <p class="modal-intro">
                Utilizamos cookies e tecnologias semelhantes para melhorar a sua experiência no site de acordo com as nossas diretrizes. Você pode personalizar suas preferências abaixo:
            </p>

            <form id="cookie-preferences-form" class="cookie-options-list">
                @foreach(config('scripts', []) as $categoryKey => $category)
                    @if(!count($category['items']))
                        @continue
                    @endif
                    <div class="cookie-category-block">
                        <label class="cookie-option-label">
                            <input type="checkbox"
                                   name="consent_{{ $categoryKey }}"
                                   id="consent_{{ $categoryKey }}"
                                   value="true"
                                   {{ ($category['level'] ?? '') === 'required' ? 'checked disabled' : '' }}>
                            <span class="category-title">{{ $category['title'] }}</span>
                        </label>
                        <p class="category-description">{{ $category['description'] }}</p>

                        <!-- Tabela de Transparência de Cookies desta Categoria -->
                        <table class="cookie-info-table">
                            <thead>
                                <tr>
                                    <th>Cookie</th>
                                    <th>Finalidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category['items'] ?? [] as $itemKey => $item)
                                    <tr>
                                        <td>
                                            <code>
                                                <!-- 💡 Se for o marcador de sessão do Laravel, busca o nome do cookie dinamicamente na renderização -->
                                                @if($item['name'] === '[session_cookie]')
                                                    {{ config('session.cookie') }}
                                                @else
                                                    {{ $item['name'] ?: $itemKey }}
                                                @endif
                                            </code>
                                        </td>
                                        <td>{{ $item['description'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </form>
        </div>

        <div class="cookie-modal-footer">
            <button id="cookie-save-preferences-btn" class="cookie-btn cookie-btn-secondary">Salvar Preferências</button>
            <button id="cookie-accept-all-modal-btn" class="cookie-btn">Aceitar Todos</button>
        </div>
    </div>
</div>

@once
@push('footer-styles')
<style>
    /* ===== BANNER PRINCIPAL ===== */
    .cookie-banner {
        position: fixed; bottom: 20px; left: 20px; right: 20px;
        background-color: var(--color-bg-card, #1a1e3a);
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        border-radius: 12px; padding: 1rem 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); z-index: 999999;
        animation: slideUp 0.4s ease-out;
    }
    @media (min-width: 640px) {
        .cookie-banner { left: 20px; right: auto; max-width: 450px; }
    }
    .cookie-content { display: flex; flex-direction: column; gap: 1rem; }
    .cookie-content p { margin: 0; font-size: 0.825rem; color: var(--color-text, #e8e6f0); line-height: 1.5; }
    .cookie-content a { color: var(--color-primary, #c8b6ff); text-decoration: underline; }
    .cookie-actions { display: flex; gap: 1rem; align-items: center; align-self: flex-end; }
    .cookie-btn-link { background: none; border: none; color: var(--color-text-muted, #8a87a8); font-size: 0.8rem; cursor: pointer; text-decoration: underline; }
    .cookie-btn { background: var(--color-primary, #c8b6ff); color: var(--color-bg-deep, #0b0d17); border: none; padding: 0.5rem 1.25rem; font-weight: 600; font-size: 0.8rem; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
    .cookie-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px var(--color-glow, rgba(157, 124, 255, 0.2)); }
    .cookie-btn-secondary { background: var(--color-bg-deep, #0b0d17); color: var(--color-text, #e8e6f0); border: 1px solid var(--color-border); }
    .cookie-btn-secondary:hover { background: var(--color-bg-card); }

    /* ===== MODAL DE PERSONALIZAÇÃO ===== */
    .cookie-modal-overlay {
        position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px); display: flex; align-items: center;
        justify-content: center; z-index: 9999999; padding: 1rem;
    }
    .cookie-modal-box {
        background: var(--color-bg-card, #1a1e3a);
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        border-radius: 12px; width: 100%; max-width: 580px;
        max-height: 85vh; display: flex; flex-direction: column;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }
    .cookie-modal-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border);
    }
    .cookie-modal-header h3 { margin: 0; font-size: 1.1rem; color: var(--color-text); }
    .cookie-modal-close { background: none; border: none; font-size: 1.5rem; color: var(--color-text-muted); cursor: pointer; }
    .cookie-modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
    .modal-intro { font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0; margin-bottom: 1.5rem; line-height: 1.6; }

    .cookie-category-block { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); }
    .cookie-category-block:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .cookie-option-label { display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 0.5rem; }
    .cookie-option-label input { width: 16px; height: 16px; cursor: pointer; }
    .category-title { font-weight: 600; font-size: 0.95rem; color: var(--color-text); }
    .category-description { font-size: 0.8rem; color: var(--color-text-muted); margin: 0 0 1rem 0; line-height: 1.5; }

    /* Tabela de Cookies */
    .cookie-info-table { width: 100%; border-collapse: collapse; font-size: 0.75rem; background: rgba(0,0,0,0.15); border-radius: 6px; overflow: hidden; }
    .cookie-info-table th, .cookie-info-table td { padding: 6px 10px; text-align: left; }
    .cookie-info-table th { background: rgba(200, 182, 255, 0.05); font-weight: 600; color: var(--color-text); border-bottom: 1px solid var(--color-border); }
    .cookie-info-table td { border-bottom: 1px solid var(--color-border); color: var(--color-text-muted); }
    .cookie-info-table tr:last-child td { border-bottom: none; }
    .cookie-info-table code { font-family: monospace; color: var(--color-primary); }

    .cookie-modal-footer {
        padding: 1rem 1.5rem; background: rgba(0, 0, 0, 0.15);
        border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 0.75rem;
    }

    @keyframes slideUp {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
@endpush
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('lgpd-cookie-banner');
        const modal = document.getElementById('lgpd-cookie-modal');
        const customizeBtn = document.getElementById('cookie-customize-btn');
        const closeBtn = document.getElementById('cookie-modal-close-btn');
        const acceptAllBtn = document.getElementById('cookie-accept-all-btn');
        const acceptAllModalBtn = document.getElementById('cookie-accept-all-modal-btn');
        const savePreferencesBtn = document.getElementById('cookie-save-preferences-btn');

        const storageKey = 'lgpd_cookie_consent_categories';

        // 1. Verifica se já existe um consentimento salvo
        const preferences = localStorage.getItem(storageKey);

        if (preferences) {
            // Se já tem preferências salvas, ativa as categorias autorizadas imediatamente
            activateAcceptedCategories(JSON.parse(preferences));
        } else {
            // Caso contrário, exibe o banner flutuante após 1 segundo
            setTimeout(() => { banner.style.display = 'block'; }, 1000);
        }

        // 2. Eventos de abertura/fechamento do Modal
        if (customizeBtn) {
            customizeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.style.display = 'flex';
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }

        // 3. Ação: "Aceitar Todos" (Tanto no banner quanto no modal)
        const aceitarTodos = () => {
            const allPreferences = {};

            // Ativa todas as categorias listadas na sua configuração PHP
            @foreach(config('scripts', []) as $key => $category)
                allPreferences['{{ $key }}'] = true;
            @endforeach

            saveAndActivate(allPreferences);
        };

        if (acceptAllBtn) acceptAllBtn.addEventListener('click', aceitarTodos);
        if (acceptAllModalBtn) acceptAllModalBtn.addEventListener('click', aceitarTodos);

        // 4. Ação: "Salvar Preferências" (Seleção granular no modal)
        if (savePreferencesBtn) {
            savePreferencesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const preferences = {};

                // 💡 Seleciona dinamicamente todos os checkboxes de consentimento
                // que começam com o id "consent_" (usando o seletor ^= do CSS)
                const checkboxes = document.querySelectorAll('input[id^="consent_"]');

                checkboxes.forEach(cb => {
                    // Extrai a chave da categoria (ex: "consent_analytics" vira "analytics")
                    const key = cb.id.replace('consent_', '');
                    preferences[key] = cb.checked;
                });

                saveAndActivate(preferences);
            });
        }

        // Função interna para salvar no localStorage, ativar os scripts e fechar a interface
        function saveAndActivate(preferencesMap) {
            // Recupera as preferências que estavam salvas anteriormente
            const oldPreferences = JSON.parse(localStorage.getItem(storageKey) || '{}');

            // Salva as novas preferências do usuário
            localStorage.setItem(storageKey, JSON.stringify(preferencesMap));

            // 💡 VERIFICAÇÃO DE MUDANÇA DE DECISÃO (OPT-OUT):
            // Se o usuário desativou alguma categoria que antes estava ativa (estava true e virou false)
            let reloadNeeded = false;
            Object.entries(preferencesMap).forEach(([category, isAccepted]) => {
                if (oldPreferences[category] === true && isAccepted === false) {
                    reloadNeeded = true;
                }
            });

            if (reloadNeeded) {
                // Recarrega a página para limpar os scripts da memória do navegador imediatamente
                window.location.reload();
                return;
            }

            // Se apenas ativou novas coisas ou manteve o mesmo estado, ativa sem precisar recarregar
            activateAcceptedCategories(preferencesMap);

            // Fecha o banner com animação suave
            if (banner) {
                banner.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                banner.style.opacity = '0';
                banner.style.transform = 'translateY(50px)';
                setTimeout(() => { banner.style.display = 'none'; }, 400);
            }

            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Função que altera o tipo dos scripts opcionais para executá-los
        function setActivation(category, isAccepted) {
            if (isAccepted) {
                const scripts = document.querySelectorAll(`script[data-cookie-category="${category}"]`);
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.src = oldScript.src;
                    newScript.type = 'text/javascript'; // Injeta o tipo executável

                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }
        }

        function activateAcceptedCategories(consents) {
            Object.entries(consents).forEach(([category, isAccepted]) => {
                setActivation(category, isAccepted);
            });
        }

        // 💡 Permite que o usuário reabra o painel a qualquer momento pelo rodapé
        const openPreferencesLink = document.getElementById('open-cookie-preferences-link');
        if (openPreferencesLink) {
            openPreferencesLink.addEventListener('click', (e) => {
                e.preventDefault();
                modal.style.display = 'flex';
            });
        }
    });
</script>
@endpush
