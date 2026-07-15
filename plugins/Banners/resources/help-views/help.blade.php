<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-image class="lucid-icon" />
            Banners
        </h3>
        <p>
            Sistema de gerenciamento de banners com imagens, links arbitrarios, estatisticas de cliques, hook dinamico e shortcodes.
        </p>
    </header>

    <h4>Como Funciona</h4>
    <p>
        Crie banners com imagem e URL de destino. Cada banner pode ser exibido automaticamente em um hook do tema,
        inserido via shortcode no conteudo, ou chamado manualmente pelo helper <code>renderBanner()</code>.
    </p>

    <h4>Exibicao Automatica (Hook)</h4>
    <p>
        Ao criar um banner, selecione um hook no campo "Ponto de Exibicao". O banner sera injetado
        automaticamente no gancho selecionado sempre que o tema renderizar aquele ponto.
    </p>

    <h4>Shortcode</h4>
    <p>Insira banners diretamente no conteudo de paginas ou posts:</p>
    <div class="code">
        [banner slug="promocao-verao"]<br>
        [banner slug="header-promo" class="rounded shadow-lg"]
    </div>
    <p>
        O atributo <code>class</code> no shortcode sobrescreve as classes definidas no banner. Se omitido,
        herda as classes configuradas na administracao.
    </p>

    <h4>Helper PHP</h4>
    <p>Use diretamente nos templates Blade:</p>
    <div class="code">
        &#123;&#123; renderBanner('nome-do-banner') &#125;&#125;<br>
        &#123;&#123; renderBanner('nome-do-banner', 'classe-custom') &#125;&#125;
    </div>

    <h4>Estatisticas de Cliques</h4>
    <p>
        Todos os cliques sao registrados com timestamp, IP e user agent. Acesse as estatisticas detalhadas
        clicando no icone de grafico na listagem de banners. O redirecionamento usa HTTP 301 (permanente).
    </p>

    <h4>Boas Praticas</h4>
    <ul>
        <li>Use slugs descritivos e unicos para facilitar o uso em shortcodes</li>
        <li>Desative banners antigos em vez de excluir para preservar o historico de cliques</li>
        <li>Prefira imagens otimizadas (WebP/JPG) para nao comprometer a performance</li>
        <li>Use o target "Nova aba" para links externos</li>
    </ul>
</div>
