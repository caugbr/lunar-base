<div class="plugin-help-content">
    <header>
        <h3><x-lucide-code-2 class="lucid-icon" /> Prism Highlight</h3>
        <p>Exibe blocos de código formatados com syntax highlighting e estética usando o Prism.js diretamente nos seus posts e páginas.</p>
    </header>

    <h4>Como funciona</h4>
    <p>O plugin intercepta o shortcode <code>[code]</code> e o renderiza como um bloco visual elegante, completo com as "bolinhas" de janela do macOS, nome do arquivo opcional e coloração de sintaxe automática (via Prism.js).</p>

    <h4>Shortcode básico</h4>
    <div class="code">[code lang="php"]
// seu código aqui
[/code]</div>

    <h4>Atributos disponíveis</h4>
    <table>
        <thead>
            <tr>
                <th>Atributo</th>
                <th>Valores Aceitos</th>
                <th>Padrão</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>lang</code></td>
                <td><code>php</code>, <code>javascript</code>, <code>html</code>, <code>css</code>, <code>bash</code>, <code>sql</code></td>
                <td><code>php</code></td>
                <td>A linguagem de programação para aplicar o destaque de sintaxe correto.</td>
            </tr>
            <tr>
                <td><code>title</code></td>
                <td>Qualquer texto (ex: <code>CauGuanabara.php</code>)</td>
                <td>—</td>
                <td>Exibe o nome do arquivo no cabeçalho do bloco, simulando uma aba de editor.</td>
            </tr>
            <tr>
                <td><code>theme</code></td>
                <td><code>dark</code>, <code>light</code></td>
                <td><code>dark</code></td>
                <td>Define o esquema de cores do bloco (fundo escuro estilo Dracula/One Dark ou claro).</td>
            </tr>
        </tbody>
    </table>

    <h4>Exemplos de uso</h4>

    <h5>Bloco simples em PHP</h5>
    <div class="code">[code lang="php"]
&lt;?php

class WebMaker {
    public function manifesto() {
        return 'Desenvolvimento é arte, não corrida.';
    }
}
?&gt;
[/code]</div>

    <h5>Bloco com nome de arquivo e tema claro</h5>
    <div class="code">[code lang="javascript" title="app.js" theme="light"]
console.log('Sem node_modules, sem frescura.');
[/code]</div>

    <h5>Snippet de CSS/SASS</h5>
    <div class="code">[code lang="css" title="style.css"]
.prism-code-block {
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
[/code]</div>

    <h4>Dicas</h4>
    <ul>
        <li><strong>Use o editor visual:</strong> Ao clicar no botão de shortcode do TinyMCE, um formulário amigável preencherá os atributos para você, sem precisar decorar a sintaxe.</li>
        <li><strong>Performance:</strong> O plugin carrega o Prism.js (motor de syntax highlighting) de forma otimizada e apenas quando um bloco de código está presente na página.</li>
        <li><strong>Segurança:</strong> O conteúdo inserido entre as tags é automaticamente escapado (sanitizado) pelo Laravel, prevenindo execução de scripts maliciosos (XSS).</li>
        <li><strong>Personalização:</strong> Se desejar alterar as cores, sombras ou fontes, você pode sobrescrever o arquivo <code>prismhighlight.css</code> no seu tema ativo do Lunar Base.</li>
    </ul>
</div>
