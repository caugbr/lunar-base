/**
 * Galleries Plugin - Lightbox vanilla JS
 * Navegação por teclado (←/→/Esc) e swipe em mobile
 */
(function () {
    'use strict';

    let lightbox = null;
    let currentGallery = null;
    let currentIndex = 0;
    let items = [];

    function buildLightbox() {
        if (lightbox) return lightbox;

        lightbox = document.createElement('div');
        lightbox.className = 'galleries-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.innerHTML = `
            <span class="galleries-lightbox-counter"></span>
            <button type="button" class="galleries-lightbox-close" aria-label="Fechar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button type="button" class="galleries-lightbox-prev" aria-label="Anterior">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>
            <img src="" alt="">
            <button type="button" class="galleries-lightbox-next" aria-label="Próxima">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
            <div class="galleries-lightbox-caption"></div>
        `;
        document.body.appendChild(lightbox);

        lightbox.querySelector('.galleries-lightbox-close').addEventListener('click', close);
        lightbox.querySelector('.galleries-lightbox-prev').addEventListener('click', prev);
        lightbox.querySelector('.galleries-lightbox-next').addEventListener('click', next);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) close();
        });
        document.addEventListener('keydown', handleKeydown);

        return lightbox;
    }

    function handleKeydown(e) {
        if (!lightbox?.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') prev();
        else if (e.key === 'ArrowRight') next();
    }

    function open(galleryEl, startIndex) {
        buildLightbox();
        currentGallery = galleryEl;
        currentIndex = startIndex;

        items = Array.from(galleryEl.querySelectorAll('.gallery-link[data-full]')).map(link => ({
            full: link.dataset.full,
            caption: link.dataset.caption || '',
            alt: link.querySelector('img')?.alt || ''
        }));

        show(currentIndex);
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function prev() {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        show(currentIndex);
    }

    function next() {
        currentIndex = (currentIndex + 1) % items.length;
        show(currentIndex);
    }

    function show(index) {
        const item = items[index];
        if (!item) return;
        const img = lightbox.querySelector('img');
        const caption = lightbox.querySelector('.galleries-lightbox-caption');
        const counter = lightbox.querySelector('.galleries-lightbox-counter');

        img.src = item.full;
        img.alt = item.alt;
        caption.textContent = item.caption;
        counter.textContent = `${index + 1} / ${items.length}`;
    }

    // Delegação de eventos: qualquer botão .gallery-link com data-full
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.gallery-link[data-full]');
        if (!link) return;

        const gallery = link.closest('.gallery[data-lightbox="true"]');
        if (!gallery) return;

        e.preventDefault();
        const allLinks = Array.from(gallery.querySelectorAll('.gallery-link[data-full]'));
        const index = allLinks.indexOf(link);
        open(gallery, index);
    });
})();
