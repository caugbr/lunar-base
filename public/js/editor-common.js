document.addEventListener('DOMContentLoaded', function() {
    // 1. Slug automático
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', debounce(function() {
            const slugField = document.getElementById('slug');
            if (slugField && !slugField.value) {
                slugField.value = slugify(this.value);
            }
        }, 2000));
    }

    // 2. Widget + is_main
    const widgetSelect = document.getElementById('widget_slug');
    const isMainSwitch = document.querySelector('input[name="is_main"]');

    if (widgetSelect && isMainSwitch) {
        function toggleIsMain() {
            const hasWidget = widgetSelect.value !== '';
            isMainSwitch.disabled = !hasWidget;
            if (!hasWidget) {
                isMainSwitch.checked = false;
                isMainSwitch.dispatchEvent(new Event('change'));
            }
        }
        toggleIsMain();
        widgetSelect.addEventListener('change', toggleIsMain);

        isMainSwitch.addEventListener('change', function() {
            const pageSlug = document.getElementById('slug');
            if (this.checked) {
                pageSlug.setAttribute('data-value', pageSlug.value);
                pageSlug.value = widgetSelect.value + '-index';
                pageSlug.setAttribute('readonly', true);
            } else {
                const oldVal = pageSlug.getAttribute('data-value');
                if (oldVal) pageSlug.value = oldVal;
                pageSlug.removeAttribute('readonly');
            }
        });
    }
});

// 3. Alpine data para thumbnail
function thumbnailManager(initial = {}) {
    return {
        thumbnailId: initial.id || null,
        thumbnailUrl: initial.url || '',

        openSelector() {
            window.dispatchEvent(new CustomEvent('modal-open', {
                detail: { id: 'selectorModal', context: 'thumbnail' }
            }));
        },

        setMedia(media) {
            this.thumbnailId = media.id;
            this.thumbnailUrl = media.thumbnail_url || media.url;
        },

        clearMedia() {
            this.thumbnailId = null;
            this.thumbnailUrl = '';
        }
    }
}

// 4. Eventos globais de Thumbnail e Upload
window.addEventListener('media:uploaded', (e) => {
    window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'mainUploader' } }));
});

window.addEventListener('media:updated', (e) => {
    window.dispatchEvent(new CustomEvent('modal-open', {
        detail: { id: 'selectorModal', context: e.detail.source }
    }));
});

window.addEventListener('media:inserted', (e) => {
    if (e.detail.source === 'thumbnail') {
        const thumbContainer = document.querySelector('[x-data^="thumbnailManager"]');
        if (thumbContainer) {
            thumbContainer._x_dataStack?.[0]?.setMedia(e.detail.media);
            window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'selectorModal' } }));
        }
    }
});

function slugify(text) {
    return text
        .toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
