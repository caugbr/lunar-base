document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#tiny-editor',
        height: 500,
        menubar: false,
        language: 'pt_BR',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        external_plugins: {
            'shortcode': '/js/tinymce/plugins/shortcode/plugin.js'
        },
        relative_urls: false,
        convert_urls: false,
        remove_script_host: true,
        toolbar: 'formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | shortcode | code | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
        forced_root_block: 'p',
        content_css: '/css/admin/tinymce-content.css',
        setup: function(editor) {
            var existingContent = document.getElementById('content').value;
            if (existingContent) {
                editor.on('init', function() {
                    editor.setContent(existingContent);
                });
            }
            var form = document.querySelector('#create_form,#edit_form');
            if (form) {
                form.addEventListener('submit', function() {
                    document.getElementById('content').value = editor.getContent();
                });
            }
        }
    });
});

// Escuta imagem para o TinyMCE
window.addEventListener('media:inserted', (e) => {
    if (e.detail.source === 'editor' && window.tinymce?.activeEditor) {
        const media = e.detail.media;
        const alignment = media.alignment ?? 'none';
        let htm = `<figure class="align-${alignment}"><img src="${media.url}" alt="${media.alt || ''}">`;
        if (media.caption) htm += `<figcaption>${media.caption}</figcaption>`;
        htm += `</figure>`;
        tinymce.activeEditor.insertContent(htm);
        window.dispatchEvent(new CustomEvent('modal-close', { detail: { id: 'selectorModal' } }));
    }
});
