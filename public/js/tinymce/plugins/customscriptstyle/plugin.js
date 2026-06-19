(function() {
  'use strict';

  tinymce.PluginManager.add('customscriptstyle', function(editor) {
    // Utilitários para codificação segura
    function encodeContent(str) {
      return btoa(unescape(encodeURIComponent(str)));
    }
    function decodeContent(str) {
      return decodeURIComponent(escape(atob(str)));
    }

    // 1. ANTES DE CARREGAR: Comentários -> Placeholders visuais
    editor.on('BeforeSetContent', function(e) {
      e.content = e.content.replace(/<!--(script|style)([^>]*)>([\s\S]*?)<\/\1-->/gi, function(match, type, attrs, body) {
        var encoded = encodeContent(body.trim());
        var label = (type === 'script' ? 'Script' : 'Estilo') + ' personalizado';
        return '<span class="tmce-custom-placeholder" data-type="' + type + '" data-attrs="' + tinymce.util.Tools.htmlEncode(attrs) + '" data-content="' + encoded + '" contenteditable="false" style="display:inline-block; background:#f8f9fa; border:1px dashed #6c757d; padding:6px 10px; cursor:pointer; font-family:monospace; font-size:13px; color:#333; margin:4px 0; border-radius:4px;">[' + label + ']</span>';
      });
    });

    // 2. ANTES DE SALVAR: Placeholders -> Comentários
    editor.on('GetContent', function(e) {
      if (e.format === 'html') {
        e.content = e.content.replace(/<span[^>]*class="tmce-custom-placeholder"[^>]*data-type="(script|style)"[^>]*data-attrs="([^"]*)"[^>]*data-content="([^"]*)"[^>]*>[\s\S]*?<\/span>/gi, function(match, type, attrs, encoded) {
          var body = decodeContent(encoded);
          return '<!--' + type + attrs + '>' + body + '</' + type + '-->';
        });
      }
    });

    // 3. CLIQUE NO PLACEHOLDER: Abre diálogo de edição
    editor.on('Click', function(e) {
      var node = editor.dom.getParent(e.target, 'span.tmce-custom-placeholder');
      if (node) {
        e.preventDefault();
        var type = node.getAttribute('data-type');
        var attrs = decodeURIComponent(node.getAttribute('data-attrs'));
        var content = decodeContent(node.getAttribute('data-content'));

        editor.windowManager.open({
          title: 'Editar ' + (type === 'script' ? 'Script' : 'Estilo'),
          body: {
            type: 'panel',
            items: [
              { type: 'textarea', name: 'content', label: 'Código', multiline: true, minHeight: 200, value: content },
              { type: 'input', name: 'attrs', label: 'Atributos extras (ex: src="url.js", type="module", media="print")', value: attrs.trim() }
            ]
          },
          buttons: [
            { type: 'cancel', name: 'closeButton', text: 'Cancelar' },
            { type: 'submit', name: 'saveButton', text: 'Salvar', primary: true }
          ],
          onSubmit: function(api) {
            var data = api.getData();
            node.setAttribute('data-content', encodeContent(data.content));
            node.setAttribute('data-attrs', tinymce.util.Tools.htmlEncode(data.attrs));
            editor.nodeChanged();
            api.close();
          }
        });
      }
    });

    // 4. BOTÃO NA TOOLBAR: Inserir novo bloco
    editor.ui.registry.addButton('customscriptstyle', {
      text: '[+ Script/Style]',
      tooltip: 'Inserir bloco de script ou estilo',
      onAction: function() {
        editor.windowManager.open({
          title: 'Inserir Script ou Estilo',
          body: {
            type: 'panel',
            items: [
              { type: 'listbox', name: 'type', label: 'Tipo', items: [{text: 'Script', value: 'script'}, {text: 'Estilo', value: 'style'}] },
              { type: 'textarea', name: 'content', label: 'Código', multiline: true, minHeight: 150, value: '' },
              { type: 'input', name: 'attrs', label: 'Atributos extras', value: '' }
            ]
          },
          buttons: [
            { type: 'cancel', name: 'closeButton', text: 'Cancelar' },
            { type: 'submit', name: 'saveButton', text: 'Inserir', primary: true }
          ],
          onSubmit: function(api) {
            var data = api.getData();
            var encoded = encodeContent(data.content);
            var label = (data.type === 'script' ? 'Script' : 'Estilo') + ' personalizado';
            var placeholder = '<span class="tmce-custom-placeholder" data-type="' + data.type + '" data-attrs="' + tinymce.util.Tools.htmlEncode(data.attrs) + '" data-content="' + encoded + '" contenteditable="false" style="display:inline-block; background:#f8f9fa; border:1px dashed #6c757d; padding:6px 10px; cursor:pointer; font-family:monospace; font-size:13px; color:#333; margin:4px 0; border-radius:4px;">[' + label + ']</span>';

            editor.insertContent(placeholder + ' ');
            api.close();
          }
        });
      }
    });

    return {};
  });
})();
