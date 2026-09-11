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
