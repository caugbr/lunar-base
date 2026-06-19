
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#tiny-editor',
        height: 500,
        menubar: false,
        language: 'pt_BR',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'customscriptstyle' // ✅ 1. Registrou o plugin
        ],
        toolbar: 'formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | link image | code | help', // ✅ 2. Adicionou o botão na toolbar
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
        forced_root_block: 'p',
        // valid_elements: 'script[*],style[*],div[*],span[*],p[*],a[*],figure[*],img[*],br,strong,em,i,b,u,h1,h2,h3,h4',
        valid_elements: 'script[*],style[*],,link[href|rel|type|media|crossorigin|integrity|as],div[*],span[*],p[*],br,hr,h1,h2,h3,h4,h5,h6,strong,b,em,i,u,sub,sup,code,pre,mark,small,del,ins,a[href|target|title|rel|class],img[*],figure[*],figcaption[*],ul[*],ol[*],li[*],table[*],thead[*],tbody[*],tr[*],td[*],th[*],blockquote[*],q[*],cite[*],iframe[src|width|height|frameborder|allow|allowfullscreen],audio[*],video[*],source[*],details[*],summary[*],button[*],svg[*]',
        extended_valid_elements: 'div[class|contenteditable|style]', // ✅ 3. Impede que o TinyMCE remova o placeholder
        content_css: '/css/tinymce-content.css',
        setup: function(editor) {
            var existingContent = document.getElementById('content').value;
            if (existingContent) {
                editor.on('init', function() {
                    editor.setContent(existingContent);
                });
            }
            var form = document.querySelector('#create_form,#edit_form');
            form.addEventListener('submit', function() {
                document.getElementById('content').value = editor.getContent();
            });
        }
    });

    // Slug automático
    document.getElementById('title').addEventListener('input', debounce(function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.value) {
            slugField.value = slugify(this.value);
        }
    }, 2000));

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

// Eventos globais de mídia

window.addEventListener('media:uploaded', (e) => {
    // Fecha upload modal
    window.dispatchEvent(new CustomEvent('modal-close', {
        detail: { id: 'mainUploader' }
    }));
});

window.addEventListener('media:updated', (e) => {
    // Abre biblioteca com mesmo contexto
    window.dispatchEvent(new CustomEvent('modal-open', {
        detail: { id: 'selectorModal', context: e.detail.source }
    }));
});

window.addEventListener('media:inserted', (e) => {
    console.log('inserted', e.detail.media)
    let shouldClose = false;
    if (e.detail.source === 'editor') {
        const media = e.detail.media;
        const alignment = media.alignment ?? 'none';
        let htm = `<figure class="align-${alignment}"><img src="${media.url}" alt="${media.alt || ''}">`;
        if (media.caption) {
            htm += `<figcaption>${media.caption}</figcaption>`;
        }
        htm += `</figure>`;
        tinymce.activeEditor.insertContent(htm);
        shouldClose = true;
    }
    if (e.detail.source === 'thumbnail') {
        const thumbContainer = document.querySelector('[x-data^="thumbnailManager"]');
        if (thumbContainer) {
            thumbContainer._x_dataStack?.[0]?.setMedia(e.detail.media);
            shouldClose = true;
        }
    }
    // Fecha o modal
    if (shouldClose) {
        window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'selectorModal' } }));
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
