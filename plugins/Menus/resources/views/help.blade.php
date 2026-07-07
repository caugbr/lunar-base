<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-menu class="lucid-icon" /> Sistema de Menus Dinâmicos
        </h3>
        <p>
            Crie e organize árvores de links hierárquicos e sub-menus aninhados (dropdowns) com mapeamento polimórfico de segurança.
        </p>
    </header>

    <h4>Links Polimórficos Dinâmicos (Anti-404)</h4>
    <p>
        Ao vincular um item de menu a uma Página ou Post do blog, o plugin não grava a URL como texto fixo, mas sim a relação direta com o registro do banco de dados. Caso o slug mude, as URLs dos menus se atualizarão automaticamente, evitando links quebrados de forma transparente!
    </p>

    <h4>Como Renderizar (Hooks e Mapeamento no Banco)</h4>
    <p>O plugin funciona integrado ao escaneador de ganchos do core do Lunar Base, criando uma ponte entre o layout e o banco:</p>

    <blockquote>
        <strong>Como a Associação Dinâmica funciona:</strong>
        <p>
            1. O desenvolvedor do tema declara o gancho no HTML (ex: <code>&lt;x-hook name="public.main_menu" desc="Menu do Topo" /&gt;</code>).<br>
            2. O administrador visualiza esse ponto no dropdown ao criar ou editar o menu e o seleciona.<br>
            3. No carregamento da página, o plugin lê essa associação no banco de dados e registra o callback de exibição de forma transparente!
        </p>
    </blockquote>

    <h4>Exemplo de Declaração no Layout:</h4>
    <div class="code">
        &lt;nav class="main-nav"&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;x-hook name="public.main_menu" desc="Menu Principal do Cabeçalho"&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;@foreach($menu ?? [] as $item)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="&#123;&#123; $item['href'] &#125;&#125;"&gt;&#123;&#123; $item['label'] &#125;&#125;&lt;/a&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;@endforeach<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;/x-hook&gt;<br>
        &lt;/nav&gt;
    </div>

    <h4>Controle Hierárquico Inteligente</h4>
    <p>
        O painel do construtor de menus possui controles de setas laterais para mover, subir, descer e aninhar links. Esse design permite gerenciar sub-menus com rapidez e sem os travamentos característicos de bibliotecas de arrastar e soltar (drag-and-drop) em dispositivos móveis.
    </p>
</div>
