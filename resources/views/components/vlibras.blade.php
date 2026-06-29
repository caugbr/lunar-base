@props([
    'position' => 'R',   // Posições: 'R' (Direita), 'L' (Esquerda), 'TL', 'TR', 'BL', 'BR', 'T', 'B'
    'avatar' => 'icaro', // Avatares: 'icaro' (masculino), 'hosana' (feminino), 'guga' ou 'random'
    'opacity' => 1.0     // Opacidade do widget (de 0.0 a 1.0)
])

@php
if (str_starts_with(setting('accessibility.position'), 'left')) {
    $position = 'L';
}
@endphp

<!-- Container do botão flutuante e do widget do VLibras -->
<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top"></div>
    </div>
</div>

@push('scripts')
<!-- Script de integração oficial do VLibras -->
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializa o widget com as propriedades recebidas do Laravel
        new window.VLibras.Widget({
            rootPath: 'https://vlibras.gov.br/app',
            position: '{{ $position }}',
            avatar: '{{ $avatar }}',
            opacity: {{ (float) $opacity }}
        });
    });
</script>
@endpush

@push('footer-styles')
<style>
    /* Garante que o botão fique acima de qualquer menu ou elemento flutuante do site */
    [vw] {
        z-index: 99999 !important;
    }

    /* Pequeno refinamento estético de transição para o botão oficial */
    .vw-access-button {
        transition: transform 0.2s ease-in-out !important;
    }
    .vw-access-button:hover {
        transform: scale(1.08) !important;
    }
</style>
@endpush
