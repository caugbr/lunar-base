(function() {
    'use strict';

    console.log("LOG: Plugin Shortcode carregado!");

    tinymce.PluginManager.add('shortcode', function(editor) {

        // Utilitário Vanilla para proteger caracteres especiais em atributos HTML
        function escapeHtml(text) {
            if (!text) return "";
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function encodeContent(str) {
            if (!str) return "";
            try { return btoa(unescape(encodeURIComponent(str))); } catch(e) { return ""; }
        }

        function decodeContent(str) {
            if (!str) return "";
            try { return decodeURIComponent(escape(atob(str))); } catch(e) { return ""; }
        }

        /**
         * 1. DB/CÓDIGO -> EDITOR VISUAL (Entrada)
         */
        editor.on('BeforeSetContent', function(e) {
            if (!e.content) return;

            // Regex Universal para capturar [tag attrs]...[/tag] ou [tag attrs]
            const pattern = /\[([a-zA-Z0-9_\-]+)((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|'[^']*'))*)\s*\](?:([\s\S]*?)\[\/\1\])?/gi;

            e.content = e.content.replace(pattern, function(match, tag, attrs, content) {
                const type = tag.toLowerCase();
                const encodedBody = encodeContent(content || "");

                // Cores para os selos
                const colors = {
                    form:   '#ec4899',
                    script: '#f97316',
                    style:  '#0ea5e9',
                    link:   '#22c55e'
                };
                const color = colors[type] || '#6366f1';

                console.log("LOG: Renderizando placeholder para:", type);

                // Usamos escapeHtml (nossa função vanilla) no lugar da tinymce.util.Tools
                return `<span class="tmce-shortcode-placeholder"
                             data-tag="${type}"
                             data-attrs="${escapeHtml(attrs || "")}"
                             data-content="${encodedBody}"
                             contenteditable="false"
                             style="display:inline-block; background:${color}10; border:1px dashed ${color}; padding:2px 10px; cursor:pointer; font-family:monospace; font-size:12px; color:${color}; margin:2px 4px; border-radius:4px; font-weight:bold; vertical-align:middle;">
                            [${type.toUpperCase()}]
                        </span>`;
            });
        });

        /**
         * 2. EDITOR VISUAL -> DB/CÓDIGO (Saída)
         */
        editor.on('GetContent', function(e) {
            if (e.format !== 'raw' && e.content) {
                const doc = new DOMParser().parseFromString(e.content, 'text/html');
                const placeholders = doc.querySelectorAll('.tmce-shortcode-placeholder');

                placeholders.forEach(el => {
                    const tag = el.getAttribute('data-tag');
                    const attrs = el.getAttribute('data-attrs');
                    const content = decodeContent(el.getAttribute('data-content'));

                    let shortcode = `[${tag}${attrs}]`;
                    // Se não for link nem form, ou se tiver conteúdo, adiciona tag de fechamento
                    if (content || !['link', 'form'].includes(tag)) {
                        shortcode += `${content}[/${tag}]`;
                    }
                    el.outerHTML = shortcode;
                });
                e.content = doc.body.innerHTML;
            }
        });

        // function openShortcodeDialog(existingNode = null) {
        //     const isEdit = !!existingNode;
        //     const data = isEdit ? {
        //         type: existingNode.getAttribute('data-tag'),
        //         attrs: existingNode.getAttribute('data-attrs').trim(),
        //         content: decodeContent(existingNode.getAttribute('data-content'))
        //     } : { type: 'form', attrs: '', content: '' };

        //     editor.windowManager.open({
        //         title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //         body: {
        //             type: 'panel',
        //             items: [
        //                 { type: 'input', name: 'type', label: 'Tag (ex: form, script, style)' },
        //                 { type: 'input', name: 'attrs', label: 'Atributos (ex: slug="contato")' },
        //                 { type: 'textarea', name: 'content', label: 'Conteúdo Interno (opcional)', minHeight: 150 }
        //             ]
        //         },
        //         initialData: data,
        //         buttons: [
        //             { type: 'cancel', text: 'Cancelar' },
        //             { type: 'submit', text: 'OK', primary: true }
        //         ],
        //         onSubmit: function(api) {
        //             const d = api.getData();
        //             // Ao inserir, inserimos o texto original. O evento BeforeSetContent cuidará de virar selo.
        //             const shortcode = `[${d.type} ${d.attrs}]${d.content}[/${d.type}]`;

        //             if (isEdit) {
        //                 editor.selection.setNode(existingNode);
        //             }
        //             editor.insertContent(shortcode);
        //             api.close();
        //         }
        //     });
        // }
function openShortcodeDialog(existingNode = null) {
            const isEdit = !!existingNode;

            // Dados iniciais para a popup
            const data = isEdit ? {
                type: existingNode.getAttribute('data-tag'),
                attrs: existingNode.getAttribute('data-attrs').trim(),
                content: decodeContent(existingNode.getAttribute('data-content'))
            } : { type: 'form', attrs: '', content: '' };

            editor.windowManager.open({
                title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
                body: {
                    type: 'panel',
                    items: [
                        { type: 'input', name: 'type', label: 'Tag (ex: form, script, style)' },
                        { type: 'input', name: 'attrs', label: 'Atributos (ex: slug="contato")' },
                        { type: 'textarea', name: 'content', label: 'Conteúdo Interno (opcional)', minHeight: 150 }
                    ]
                },
                initialData: data,
                buttons: [
                    { type: 'cancel', text: 'Cancelar' },
                    { type: 'submit', text: 'OK', primary: true }
                ],
                onSubmit: function(api) {
                    const d = api.getData();

                    // Monta o texto do shortcode respeitando a intenção (form/link costumam ser tags únicas)
                    let shortcode = `[${d.type.trim()} ${d.attrs.trim()}]`;
                    if (d.content.trim() || !['link', 'form'].includes(d.type.trim())) {
                        shortcode += `${d.content.trim()}[/${d.type.trim()}]`;
                    }

                    if (isEdit) {
                        // 1. Forçamos a seleção do nó antigo
                        editor.selection.select(existingNode);
                        // 2. Removemos o nó antigo do DOM antes de inserir o novo
                        editor.dom.remove(existingNode);
                    }

                    // 3. Inserimos o novo conteúdo (o BeforeSetContent vai transformar em selo rosa na hora)
                    editor.insertContent(shortcode);

                    api.close();
                }
            });
        }

        editor.on('Click', function(e) {
            const node = editor.dom.getParent(e.target, 'span.tmce-shortcode-placeholder');
            if (node) {
                e.preventDefault();
                openShortcodeDialog(node);
            }
        });

        editor.ui.registry.addButton('shortcode', {
            text: '{ } Shortcode',
            tooltip: 'Inserir Shortcode',
            onAction: function() {
                openShortcodeDialog();
            }
        });
    });
})();
