/**
 * Adiciona uma barra de rolagem horizontal espelhada no topo de qualquer elemento.
 *
 * @param {string|HTMLElement} target Seletor CSS ou elemento DOM
 * @param {Object} options Configurações opcionais { sticky: false, height: '14px' }
 */
function topScroll(target, options = {}) {
    const elements = typeof target === 'string'
        ? document.querySelectorAll(target)
        : (target instanceof NodeList || Array.isArray(target) ? target : [target]);

    elements.forEach(el => {
        // Evita aplicar duas vezes no mesmo elemento
        if (!el || el._hasTopScroll) return;
        el._hasTopScroll = true;

        // Cria a barra do topo e estiliza via inline (zero dependência de CSS externo)
        const topWrapper = document.createElement('div');
        topWrapper.className = 'top-scroll-track';
        Object.assign(topWrapper.style, {
            overflowX: 'auto',
            overflowY: 'hidden',
            height: options.height || '14px',
            width: '100%',
            display: 'none', // Fica oculta até detectar que há rolagem
            marginBottom: '4px',
            ...(options.sticky ? { position: 'sticky', top: '0', zIndex: '20' } : {})
        });

        // Cria o elemento invisível interno
        const dummy = document.createElement('div');
        dummy.style.height = '1px';
        topWrapper.appendChild(dummy);

        // Insere a barra exatamente antes da tabela
        el.parentNode.insertBefore(topWrapper, el);

        // Sincroniza tamanho e esconde a barra se a tela for larga o suficiente
        const syncSize = () => {
            const hasOverflow = el.scrollWidth > el.clientWidth;
            topWrapper.style.display = hasOverflow ? 'block' : 'none';
            dummy.style.width = el.scrollWidth + 'px';
        };

        const observer = new ResizeObserver(syncSize);
        observer.observe(el);
        if (el.firstElementChild) observer.observe(el.firstElementChild);

        // Sincronização bidirecional do scroll (sem loop infinito)
        let isSyncingTop = false;
        let isSyncingEl = false;

        topWrapper.addEventListener('scroll', () => {
            if (!isSyncingTop) {
                isSyncingEl = true;
                el.scrollLeft = topWrapper.scrollLeft;
            }
            isSyncingTop = false;
        }, { passive: true });

        el.addEventListener('scroll', () => {
            if (!isSyncingEl) {
                isSyncingTop = true;
                topWrapper.scrollLeft = el.scrollLeft;
            }
            isSyncingEl = false;
        }, { passive: true });
    });
}
