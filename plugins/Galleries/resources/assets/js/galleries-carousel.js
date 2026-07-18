/**
 * Galleries Plugin - Carousel Navigation
 * Navegação por botões, teclado e swipe em mobile
 */
(function () {
    'use strict';

    // Inicializa todos os carousels da página
    function initCarousels() {
        const carousels = document.querySelectorAll('.gallery-carousel');

        carousels.forEach(carousel => {
            initCarousel(carousel);
        });
    }

    function initCarousel(carousel) {
        const track = carousel.querySelector('.gallery-carousel-track');
        const prevBtn = carousel.querySelector('.carousel-btn.prev');
        const nextBtn = carousel.querySelector('.carousel-btn.next');

        if (!track) return;

        // Configurações
        const itemWidth = 300; // Largura fixa do item (definida no CSS)
        const gap = parseInt(getComputedStyle(carousel).getPropertyValue('--gallery-gap')) || 8;
        const scrollAmount = itemWidth + gap;

        // Botão anterior
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                track.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            });
        }

        // Botão próximo
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            });
        }

        // Swipe em mobile
        let touchStartX = 0;
        let touchEndX = 0;

        track.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        track.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe para esquerda → próximo
                    track.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                } else {
                    // Swipe para direita → anterior
                    track.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                }
            }
        }

        // Navegação por teclado (setas)
        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                track.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else if (e.key === 'ArrowRight') {
                track.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        });

        // Mostrar/esconder botões baseado na posição do scroll
        function updateButtonVisibility() {
            const maxScroll = track.scrollWidth - track.clientWidth;
            const currentScroll = track.scrollLeft;

            if (prevBtn) {
                prevBtn.style.opacity = currentScroll > 0 ? '1' : '0.5';
                prevBtn.style.pointerEvents = currentScroll > 0 ? 'auto' : 'none';
            }

            if (nextBtn) {
                nextBtn.style.opacity = currentScroll < maxScroll - 1 ? '1' : '0.5';
                nextBtn.style.pointerEvents = currentScroll < maxScroll - 1 ? 'auto' : 'none';
            }
        }

        track.addEventListener('scroll', updateButtonVisibility);
        window.addEventListener('resize', updateButtonVisibility);

        // Inicializa visibilidade
        updateButtonVisibility();

        // Torna o carousel focável para navegação por teclado
        carousel.setAttribute('tabindex', '0');
        carousel.setAttribute('role', 'region');
        carousel.setAttribute('aria-label', 'Galeria em carrossel');
    }

    // Inicializa quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousels);
    } else {
        initCarousels();
    }

    // Re-inicializa se necessário (para conteúdo dinâmico)
    window.initGalleriesCarousel = initCarousels;
})();
