document.addEventListener('DOMContentLoaded', function() {
    // Slug automático
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', debounce(function() {
            const slugField = document.getElementById('slug');
            if (slugField && !slugField.value) {
                slugField.value = slugify(this.value);
            }
        }, 2000));
    }

    // Widget + is_main
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

// Alpine data para thumbnail
function thumbnailManager(initial = {}) {
    return {
        thumbnailId: initial.id || null,
        thumbnailUrl: initial.url || '',

        openSelector() {
            openGridModal('thumbnail', false, initial.id || null);
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

window.gridIsMultiple = false;
window.openGridModal = function(
    context,
    multiple = window.gridIsMultiple,
    selected = [],
    labels = {},
    details = {}
) {

    labels = {
        ...{
            title: 'Selecionar Mídia',
            insert: 'Inserir',
            bulkInsert: 'Inserir selecionados',
            clear: 'Limpar',
            close: 'Fechar'
        },
        ...labels
    };
    window.dispatchEvent(new CustomEvent('media:set-labels', { detail: { labels } }));
    window.dispatchEvent(new CustomEvent('modal-set-title', {
        detail: { id: 'selectorModal', title: labels.title }
    }));
    window.dispatchEvent(new CustomEvent('modal-set-close-label', {
        detail: { id: 'selectorModal', closeLabel: labels.close }
    }));

    // Guarda a última opção ao abrir o modal
    window.gridIsMultiple = multiple;

    // Configura o grid para aceitar ou não seleção múltipla
    window.dispatchEvent(new CustomEvent('media:set-multiple', { detail: { multiple } }));

    // Configura o grid para mostrar imagens já seleionadas
    window.dispatchEvent(new CustomEvent('media:set-selected', { detail: { selected } }));

    // Abre o modal padrão do seletor
    window.dispatchEvent(new CustomEvent('modal-open', {
        detail: { id: 'selectorModal', context, ...details }
    }));
};

// Eventos globais de Thumbnail e Upload
window.addEventListener('media:uploaded', (e) => {
    window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'mainUploader' } }));
});

window.addEventListener('media:updated', (e) => {
    openGridModal(e.detail.source);
    // window.dispatchEvent(new CustomEvent('modal-open', {
    //     detail: { id: 'selectorModal', context: e.detail.source }
    // }));
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
