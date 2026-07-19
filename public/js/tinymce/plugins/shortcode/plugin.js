(function() {
    'use strict';

    console.log("LOG: Plugin Shortcode Dinâmico carregado!");

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
         * Converte uma string de atributos (ex: slug="contato" columns="3") em um objeto JS
         */
        function parseAttributesJS(attrsString) {
            const attributes = {};
            if (!attrsString) return attributes;
            // Regex robusta para capturar pares de chave="valor" ou chave='valor'
            const regex = /([\w:]+)\s*=\s*(?:"([^"]*)"|'([^']*)')/g;
            let match;
            while ((match = regex.exec(attrsString)) !== null) {
                attributes[match[1]] = match[2] !== undefined ? match[2] : match[3];
            }
            return attributes;
        }

        /**
         * 1. DB/CÓDIGO -> EDITOR VISUAL (Entrada)
         */
        editor.on('BeforeSetContent', function(e) {
            if (!e.content) return;

            const pattern = /\[([a-zA-Z0-9_\-]+)((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|'[^']*'))*)\s*\](?:([\s\S]*?)\[\/\1\])?/gi;

            e.content = e.content.replace(pattern, function(match, tag, attrs, content) {
                const type = tag.toLowerCase();
                const encodedBody = encodeContent(content || "");

                // Cores para os selos visuais
                const colors = {
                    form:   '#ec4899',
                    script: '#f97316',
                    style:  '#0ea5e9',
                    link:   '#22c55e',
                    embed:  '#a855f7'
                };
                const color = colors[type] || '#6366f1';

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
                    if (content || !['link', 'form'].includes(tag)) {
                        shortcode += `${content}[/${tag}]`;
                    }
                    el.outerHTML = shortcode;
                });
                e.content = doc.body.innerHTML;
            }
        });

        /**
         * 3. CONSTRUTOR DINÂMICO DE ESTRUTURA DO MODAL
         */
        function getDialogStructure(selectedType, customAttrCount) {
            const shortcodes = window.LUNAR_SHORTCODES || {};
            const isRegistered = shortcodes[selectedType] !== undefined;
            const items = [];

            // Dropdown de Tags (Lê o objeto global window.LUNAR_SHORTCODES)
            const typeItems = [
                { value: '', text: '-- Selecione o Shortcode --' },
                ...Object.keys(shortcodes).map(tag => ({
                    value: tag,
                    text: `[${tag.toUpperCase()}] - ${shortcodes[tag].description || ''}`
                })),
                { value: 'custom', text: 'Personalizado / Outro...' }
            ];

            items.push({
                type: 'selectbox',
                name: 'type',
                label: 'Selecione o Shortcode',
                items: typeItems
            });

            // Se for personalizado, exibe campo de texto livre para digitar a tag
            if (selectedType === 'custom') {
                items.push({
                    type: 'input',
                    name: 'custom_tag_name',
                    label: 'Nome da Tag (ex: meu_plugin)'
                });
            }

            // Se for um shortcode registrado, constrói os inputs com base no Schema de Atributos
            if (isRegistered) {
                const schemaAttrs = shortcodes[selectedType].attributes || {};
                Object.entries(schemaAttrs).forEach(([attrName, attr]) => {
                    const fieldName = 'attr_' + attrName;
                    const label = attr.label + (attr.required ? ' *' : '');

                    if (attr.type === 'select') {
                        const selectItems = Object.entries(attr.options || {}).map(([val, text]) => ({
                            value: val,
                            text: text
                        }));
                        items.push({
                            type: 'selectbox',
                            name: fieldName,
                            label: label,
                            items: selectItems
                        });
                    } else if (attr.type === 'checkbox') {
                        items.push({
                            type: 'checkbox',
                            name: fieldName,
                            label: label
                        });
                    } else if (attr.type === 'textarea') {
                        items.push({
                            type: 'textarea',
                            name: fieldName,
                            label: label,
                            placeholder: attr.placeholder || ''
                        });
                    } else {
                        // text, number, etc.
                        items.push({
                            type: 'input',
                            name: fieldName,
                            label: label,
                            placeholder: attr.placeholder || ''
                        });
                    }
                });
            }

            // Se houver shortcode selecionado, exibe a seção de Atributos Adicionais [Chave] = [Valor]
            if (selectedType) {
                items.push({
                    type: 'htmlpanel',
                    html: '<hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;" />'
                });

                items.push({
                    type: 'htmlpanel',
                    html: '<div style="font-weight: bold; margin-bottom: 0.5rem; font-size: 13px;">Atributos Personalizados Adicionais</div>'
                });

                // Constrói dinamicamente o par de campos conforme o contador
                for (let i = 1; i <= customAttrCount; i++) {
                    items.push({
                        type: 'grid',
                        columns: 2,
                        items: [
                            { type: 'input', name: `custom_key_${i}`, label: `Chave ${i} (ex: class)` },
                            { type: 'input', name: `custom_val_${i}`, label: `Valor ${i} (ex: highlight)` }
                        ]
                    });
                }

                // Botão "Novo Atributo" nativo do TinyMCE (Dispara onAction)
                items.push({
                    type: 'button',
                    name: 'add_custom_attr',
                    text: '+ Adicionar Atributo Personalizado',
                    border: true
                });
            }

            // Exibe o conteúdo interno (apenas se a tag não for uma das restritas do core)
            const hideContent = ['link', 'form'].includes(selectedType);
            if (!hideContent && selectedType) {
                items.push({
                    type: 'htmlpanel',
                    html: '<hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;" />'
                });
                items.push({
                    type: 'textarea',
                    name: 'content',
                    label: 'Conteúdo Interno (opcional)',
                    minHeight: 120
                });
            }

            return {
                type: 'panel',
                items: items
            };
        }

        // function openShortcodeDialog(existingNode = null) {
        //     const isEdit = !!existingNode;
        //     const shortcodes = window.LUNAR_SHORTCODES || {};

        //     let initialData = { type: '', content: '' };
        //     let activeCustomAttrCount = 0;

        //     /**
        //      * MAPEAMENTO BIDIRECIONAL (Entrada para Edição)
        //      */
        //     if (isEdit) {
        //         const tag = existingNode.getAttribute('data-tag');
        //         const attrsString = existingNode.getAttribute('data-attrs');
        //         const parsedAttrs = parseAttributesJS(attrsString);
        //         const isRegistered = shortcodes[tag] !== undefined;

        //         initialData.type = isRegistered ? tag : 'custom';
        //         initialData.content = decodeContent(existingNode.getAttribute('data-content'));

        //         if (!isRegistered) {
        //             initialData.custom_tag_name = tag;
        //         }

        //         const unrecognizedAttrs = {};

        //         if (isRegistered) {
        //             const schemaAttrs = shortcodes[tag].attributes || {};

        //             // 1. Aplica os valores padrão do esquema primeiro
        //             Object.entries(schemaAttrs).forEach(([attrName, attr]) => {
        //                 initialData['attr_' + attrName] = attr.default !== undefined ? attr.default : '';
        //             });

        //             // 2. Preenche com os valores reais salvos, separando o que for desconhecido
        //             Object.entries(parsedAttrs).forEach(([key, val]) => {
        //                 if (schemaAttrs[key] !== undefined) {
        //                     if (schemaAttrs[key].type === 'checkbox') {
        //                         initialData['attr_' + key] = (val === '1' || val === 'true' || val === true);
        //                     } else {
        //                         initialData['attr_' + key] = val;
        //                     }
        //                 } else {
        //                     unrecognizedAttrs[key] = val;
        //                 }
        //             });
        //         } else {
        //             // Se for personalizado, todos os atributos são tratados como desconhecidos
        //             Object.assign(unrecognizedAttrs, parsedAttrs);
        //         }

        //         // 3. Joga os atributos não reconhecidos pelo esquema nos inputs dinâmicos [Chave] = [Valor]
        //         Object.entries(unrecognizedAttrs).forEach(([key, val]) => {
        //             activeCustomAttrCount++;
        //             initialData[`custom_key_${activeCustomAttrCount}`] = key;
        //             initialData[`custom_val_${activeCustomAttrCount}`] = val;
        //         });
        //     }

        //     let currentCustomAttrCount = activeCustomAttrCount;
        //     let selectedType = initialData.type || '';

        //     // 4. Abertura do Modal do TinyMCE
        //     editor.windowManager.open({
        //         title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //         body: getDialogStructure(selectedType, currentCustomAttrCount),
        //         initialData: initialData,
        //         buttons: [
        //             { type: 'cancel', text: 'Cancelar' },
        //             { type: 'submit', text: 'OK', primary: true }
        //         ],

        //         // Disparado quando qualquer campo muda (escuta o select de Tipo)
        //         onChange: function(api, details) {
        //             if (details.name === 'type') {
        //                 const data = api.getData();
        //                 selectedType = data.type;

        //                 // Redesenha a janela mantendo os dados digitados nos campos correspondentes
        //                 // api.redraft({
        //                 //     title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //                 //     body: getDialogStructure(selectedType, currentCustomAttrCount),
        //                 //     buttons: [
        //                 //         { type: 'cancel', text: 'Cancelar' },
        //                 //         { type: 'submit', text: 'OK', primary: true }
        //                 //     ]
        //                 // });
        //                 api.redial({
        //                     title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //                     body: getDialogStructure(selectedType, currentCustomAttrCount),
        //                     buttons: [
        //                         { type: 'cancel', text: 'Cancelar' },
        //                         { type: 'submit', text: 'OK', primary: true }
        //                     ]
        //                 });
        //             }
        //         },

        //         // Disparado quando botões internos são clicados (Novo Atributo)
        //         onAction: function(api, details) {
        //             if (details.name === 'add_custom_attr') {
        //                 currentCustomAttrCount++;

        //                 // api.redraft({
        //                 //     title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //                 //     body: getDialogStructure(selectedType, currentCustomAttrCount),
        //                 //     buttons: [
        //                 //         { type: 'cancel', text: 'Cancelar' },
        //                 //         { type: 'submit', text: 'OK', primary: true }
        //                 //     ]
        //                 // });
        //                 api.redial({
        //                     title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
        //                     body: getDialogStructure(selectedType, currentCustomAttrCount),
        //                     buttons: [
        //                         { type: 'cancel', text: 'Cancelar' },
        //                         { type: 'submit', text: 'OK', primary: true }
        //                     ]
        //                 });
        //             }
        //         },

        //         // Disparado na submissão
        //         onSubmit: function(api) {
        //             const d = api.getData();
        //             const finalTag = d.type === 'custom' ? d.custom_tag_name.trim() : d.type;

        //             if (!finalTag) {
        //                 editor.notificationManager.open({ text: 'Por favor, especifique a tag do shortcode.', type: 'error' });
        //                 return;
        //             }

        //             let attrsString = '';

        //             // 1. Processa os atributos mapeados do esquema
        //             Object.keys(d).forEach(key => {
        //                 if (key.startsWith('attr_')) {
        //                     const attrName = key.replace('attr_', '');
        //                     const val = d[key];
        //                     if (typeof val === 'boolean') {
        //                         if (val) attrsString += ` ${attrName}="1"`;
        //                     } else if (val !== undefined && val !== '') {
        //                         attrsString += ` ${attrName}="${val}"`;
        //                     }
        //                 }
        //             });

        //             // 2. Processa os atributos personalizados dinâmicos [Chave] = [Valor]
        //             for (let i = 1; i <= currentCustomAttrCount; i++) {
        //                 const key = d[`custom_key_${i}`];
        //                 const val = d[`custom_val_${i}`];
        //                 if (key && key.trim()) {
        //                     attrsString += ` ${key.trim()}="${val || ''}"`;
        //                 }
        //             }

        //             // 3. Monta o shortcode final
        //             let shortcode = `[${finalTag}${attrsString}]`;
        //             if ((d.content && d.content.trim()) || !['link', 'form'].includes(finalTag)) {
        //                 shortcode += `${(d.content || '').trim()}[/${finalTag}]`;
        //             }

        //             if (isEdit) {
        //                 editor.selection.select(existingNode);
        //                 editor.dom.remove(existingNode);
        //             }

        //             editor.insertContent(shortcode);
        //             api.close();
        //         }
        //     });
        // }
function openShortcodeDialog(existingNode = null) {
            const isEdit = !!existingNode;
            const shortcodes = window.LUNAR_SHORTCODES || {};

            let initialData = { type: '', content: '' };
            let activeCustomAttrCount = 0;

            /**
             * MAPEAMENTO BIDIRECIONAL (Entrada para Edição)
             */
            if (isEdit) {
                const tag = existingNode.getAttribute('data-tag');
                const attrsString = existingNode.getAttribute('data-attrs');
                const parsedAttrs = parseAttributesJS(attrsString);
                const isRegistered = shortcodes[tag] !== undefined;

                initialData.type = isRegistered ? tag : 'custom';
                initialData.content = decodeContent(existingNode.getAttribute('data-content'));

                if (!isRegistered) {
                    initialData.custom_tag_name = tag;
                }

                const unrecognizedAttrs = {};

                if (isRegistered) {
                    const schemaAttrs = shortcodes[tag].attributes || {};

                    Object.entries(schemaAttrs).forEach(([attrName, attr]) => {
                        initialData['attr_' + attrName] = attr.default !== undefined ? attr.default : '';
                    });

                    Object.entries(parsedAttrs).forEach(([key, val]) => {
                        if (schemaAttrs[key] !== undefined) {
                            if (schemaAttrs[key].type === 'checkbox') {
                                initialData['attr_' + key] = (val === '1' || val === 'true' || val === true);
                            } else {
                                initialData['attr_' + key] = val;
                            }
                        } else {
                            unrecognizedAttrs[key] = val;
                        }
                    });
                } else {
                    Object.assign(unrecognizedAttrs, parsedAttrs);
                }

                Object.entries(unrecognizedAttrs).forEach(([key, val]) => {
                    activeCustomAttrCount++;
                    initialData[`custom_key_${activeCustomAttrCount}`] = key;
                    initialData[`custom_val_${activeCustomAttrCount}`] = val;
                });
            }

            let currentCustomAttrCount = activeCustomAttrCount;
            let selectedType = initialData.type || '';

            /**
             * 💡 SOLUÇÃO: Função que gera a configuração unificada do diálogo.
             * Garante que os métodos onChange, onAction e onSubmit persistam após cada redial().
             */
            function getDialogConfig() {
                return {
                    title: (isEdit ? 'Editar ' : 'Inserir ') + 'Shortcode',
                    body: getDialogStructure(selectedType, currentCustomAttrCount),
                    buttons: [
                        { type: 'cancel', text: 'Cancelar' },
                        { type: 'submit', text: 'OK', primary: true }
                    ],
                    initialData: initialData, // Preserva os dados atuais dos inputs

                    // Captura mudanças nos campos (ex: alteração de Tag no select)
                    onChange: function(api, details) {
                        if (details.name === 'type') {
                            // Captura os dados digitados até o momento para não perdê-los no redial
                            initialData = api.getData();
                            selectedType = initialData.type;

                            // Re-desenha o modal com o novo esquema
                            api.redial(getDialogConfig());
                        }
                    },

                    // Captura cliques em botões personalizados (ex: Adicionar Atributo)
                    onAction: function(api, details) {
                        if (details.name === 'add_custom_attr') {
                            // Captura os dados atuais antes de incrementar os campos
                            initialData = api.getData();
                            currentCustomAttrCount++;

                            // Re-desenha o modal adicionando a nova linha
                            api.redial(getDialogConfig());
                        }
                    },

                    // Executado no clique do botão de submissão (OK)
                    onSubmit: function(api) {
                        const d = api.getData();
                        const finalTag = d.type === 'custom' ? d.custom_tag_name.trim() : d.type;

                        if (!finalTag) {
                            editor.notificationManager.open({ text: 'Por favor, especifique a tag do shortcode.', type: 'error' });
                            return;
                        }

                        let attrsString = '';

                        // 1. Processa os atributos mapeados do esquema
                        Object.keys(d).forEach(key => {
                            if (key.startsWith('attr_')) {
                                const attrName = key.replace('attr_', '');
                                const val = d[key];
                                if (typeof val === 'boolean') {
                                    if (val) attrsString += ` ${attrName}="1"`;
                                } else if (val !== undefined && val !== '') {
                                    attrsString += ` ${attrName}="${val}"`;
                                }
                            }
                        });

                        // 2. Processa os atributos personalizados dinâmicos [Chave] = [Valor]
                        for (let i = 1; i <= currentCustomAttrCount; i++) {
                            const key = d[`custom_key_${i}`];
                            const val = d[`custom_val_${i}`];
                            if (key && key.trim()) {
                                attrsString += ` ${key.trim()}="${val || ''}"`;
                            }
                        }

                        // 3. Monta o shortcode final
                        let shortcode = `[${finalTag}${attrsString}]`;
                        if ((d.content && d.content.trim()) || !['link', 'form'].includes(finalTag)) {
                            shortcode += `${(d.content || '').trim()}[/${finalTag}]`;
                        }

                        if (isEdit) {
                            editor.selection.select(existingNode);
                            editor.dom.remove(existingNode);
                        }

                        editor.insertContent(shortcode);
                        api.close();
                    }
                };
            }

            // Abre o modal inicialmente utilizando a configuração unificada
            editor.windowManager.open(getDialogConfig());
        }

        editor.on('Click', function(e) {
            const node = editor.dom.getParent(e.target, 'span.tmce-shortcode-placeholder');
            if (node) {
                e.preventDefault();
                openShortcodeDialog(node);
            }
        });

        editor.ui.registry.addButton('shortcode', {
            text: '[/]',
            tooltip: 'Inserir Shortcode',
            onAction: function() {
                openShortcodeDialog();
            }
        });
    });
})();
