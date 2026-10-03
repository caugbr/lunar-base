@props([
    'perPage' => 12,
    'initialType' => 'image',
    'initialLinked' => 'all'
])

<x-modal id="selectorModal" title="Selecionar Mídia" size="xl">
    <x-media.grid
        id="gridInsideModal"
        :selectable="true"
        :multiple="false"
        :per-page="$perPage"
        :initial-type="$initialType"
        :initial-linked="$initialLinked"
    />
</x-modal>

<script>
window.gridIsMultiple = false;

// window.openGridModal = function(
//     context,
//     multiple = window.gridIsMultiple,
//     selected = [],
//     labels = {},
//     details = {}
// ) {

//     labels = {
//         ...{
//             title: 'Selecionar Mídia',
//             insert: 'Inserir',
//             bulkInsert: 'Inserir selecionados',
//             clear: 'Limpar',
//             close: 'Fechar'
//         },
//         ...labels
//     };
//     window.dispatchEvent(new CustomEvent('media:set-labels', { detail: { labels } }));
//     window.dispatchEvent(new CustomEvent('modal-set-title', {
//         detail: { id: 'selectorModal', title: labels.title }
//     }));
//     window.dispatchEvent(new CustomEvent('modal-set-close-label', {
//         detail: { id: 'selectorModal', closeLabel: labels.close }
//     }));

//     // Guarda a última opção ao abrir o modal
//     window.gridIsMultiple = multiple;

//     // Configura o grid para aceitar ou não seleção múltipla
//     window.dispatchEvent(new CustomEvent('media:set-multiple', { detail: { multiple } }));

//     // Configura o grid para mostrar imagens já seleionadas
//     window.dispatchEvent(new CustomEvent('media:set-selected', { detail: { selected } }));

//     // Abre o modal padrão do seletor
//     window.dispatchEvent(new CustomEvent('modal-open', {
//         detail: { id: 'selectorModal', context, ...details }
//     }));
// };
window.openGridModal = function(
    context,
    multiple = window.gridIsMultiple,
    selected = [],
    labels = {},
    details = {}
) {
    labels = {
        title: 'Selecionar Mídia',
        insert: 'Inserir',
        bulkInsert: 'Inserir selecionados',
        clear: 'Limpar',
        close: 'Fechar',
        ...labels
    };

    window.dispatchEvent(new CustomEvent('media:set-labels', { detail: { labels } }));
    window.dispatchEvent(new CustomEvent('modal-set-title', {
        detail: { id: 'selectorModal', title: labels.title }
    }));
    window.dispatchEvent(new CustomEvent('modal-set-close-label', {
        detail: { id: 'selectorModal', closeLabel: labels.close }
    }));

    window.gridIsMultiple = multiple;
    window.dispatchEvent(new CustomEvent('media:set-multiple', { detail: { multiple } }));

    // 🚀 PASSA OS DETALHES COMPLETOS NO PRÓPRIO EVENTO DE ABERTURA:
    window.dispatchEvent(new CustomEvent('modal-open', {
        detail: {
            id: 'selectorModal',
            context,
            selected: selected, // Envia exatamente o que foi pedido (vazio se for Tiptap)
            allowSizeSelect: details.allowSizeSelect !== undefined ? details.allowSizeSelect : true,
            fixedSize: details.fixedSize || null,
            ...details
        }
    }));
};
</script>
