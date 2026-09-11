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
</script>
