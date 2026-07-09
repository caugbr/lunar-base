<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-map class="lucid-icon" />
            Maps
        </h3>
        <p>
            Crie mapas interativos OpenStreetMap com marcadores personalizados e exiba-os em posts ou páginas através de shortcodes.
        </p>
    </header>

    <h4>Criando um novo mapa</h4>
    <p>
        Acesse <strong>Admin → Plugins → Maps</strong> e clique em <strong>Novo Mapa</strong>. Preencha o título, a descrição opcional e ajuste as configurações centrais (latitude, longitude e zoom).
    </p>

    <h4>Adicionando marcadores</h4>
    <p>
        Você pode adicionar pinos de três formas:
    </p>
    <ul>
        <li>Clique no botão <strong>Adicionar</strong> na aba <em>Marcadores</em>.</li>
        <li>Clique diretamente no preview do mapa.</li>
        <li>Arraste um marcador existente para reposicioná-lo.</li>
    </ul>
    <p>
        Cada marcador aceita um <strong>título</strong>, <strong>conteúdo em HTML</strong> (exibido no popup) e uma <strong>cor personalizada</strong>.
    </p>

    <h4>Buscando endereços</h4>
    <p>
        Use a busca integrada com a API Nominatim (OpenStreetMap) para encontrar coordenadas a partir de um endereço. Os resultados atualizam o centro do mapa automaticamente.
    </p>

    <h4>Exibindo o mapa no site</h4>
    <p>
        Depois de salvar, copie o shortcode gerado na aba <em>Shortcode</em>:
    </p>
    <p class="code">
        <code>[map id="1"]</code>
    </p>
    <p>
        Cole esse shortcode em qualquer post ou página para renderizar o mapa automaticamente no conteúdo público.
    </p>

    <h4>Configurações do mapa</h4>
    <ul>
        <li><strong>Zoom:</strong> nível inicial de aproximação (1 a 18).</li>
        <li><strong>Altura:</strong> altura do mapa em pixels.</li>
        <li><strong>Controles de zoom:</strong> exibe/esconde os botões de zoom.</li>
        <li><strong>Permitir arrastar:</strong> habilita/desabilita o arraste do mapa.</li>
        <li><strong>Zoom com scroll:</strong> permite aproximar com a roda do mouse.</li>
    </ul>
</div>
