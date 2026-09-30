@props([
    'avatar' => 'icaro',
    'opacity' => 1.0
])

<a href="#"
    id="custom-vlibras-btn"
    class="accessibility-btn"
    title="Acessibilidade em Libras">
    <img style="width: 20px; height: 20px;" src="{{ asset('images/vlibras.png') }}" alt="Vlibras">
</a>

@push('scripts')
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        new window.VLibras.Widget('https://vlibras.gov.br/app');
        const vla = document.getElementById('custom-vlibras-btn');
        vla.addEventListener('click', event => {
            event.preventDefault();
            toggleVLibras();
        })
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

    #custom-vlibras-btn {
        text-decoration: none;
        display: inline-flex;
        width: 32px;
        height: 32px;
        justify-content: center;
        align-items: center;
        border: 1px solid currentColor;
        border-radius: 6px;
        transition: background-color 0.2s, color 0.2s, transform 0.1s;
        background: transparent;
        cursor: pointer;
    }

    #custom-vlibras-btn:active {
        transform: scale(0.95);
    }
</style>
@endpush
