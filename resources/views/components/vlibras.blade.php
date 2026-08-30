@props([
    'avatar' => 'icaro',
    'opacity' => 1.0
])

<!-- O seu botão estilizado perfeitamente integrado à sua barra de acessibilidade -->
<button type="button"
        id="custom-vlibras-btn"
        class="accessibility-btn"
        title="Acessibilidade em Libras"
        onclick="toggleVLibras()"
        style="display: inline-flex; width:32px; height: 32px; justify-content: center; align-items: center; border-radius: 6px; border: 1px solid currentColor; background: transparent; cursor: pointer;">
    <img style="width: 20px; height: 20px;" src="{{ asset('images/vlibras.png') }}" alt="Vlibras">
</button>

@push('scripts')
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    });

    // Função que clica no botão oficial dentro do Shadow DOM
    function toggleVLibras() {
        const shadowHost = document.getElementById('vlibras-access-wrapper');
        if (shadowHost && shadowHost.shadowRoot) {
            const btn = shadowHost.shadowRoot.querySelector('button, [vw-access-button], .vw-access-button');
            if (btn) {
                btn.click();
                return;
            }
        }

        // Fallback para versões antigas
        const fallbackBtn = document.querySelector('[vw-access-button]');
        if (fallbackBtn) fallbackBtn.click();
    }
</script>
@endpush

@push('footer-styles')
<style>
    /* Esconde o botão flutuante padrão do VLibras para usar apenas o da sua barra */
    #vlibras-access-wrapper,
    [vw-access-button] {
        display: none !important;
    }

    /* Mantém a janela do avatar visível quando o widget for aberto */
    .vp-container,
    [vw-plugin-wrapper] {
        display: block !important;
    }
</style>
@endpush
