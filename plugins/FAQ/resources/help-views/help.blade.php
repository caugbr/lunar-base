<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-file-question-mark class="lucid-icon" /> Sistema de Perguntas Frequentes (FAQ)
        </h3>
        <p>
            Crie blocos de perguntas e respostas de forma organizada, exibindo sanfonas (acordeões) nativos do navegador de forma rápida e responsiva.
        </p>
    </header>

    <h4>Armazenamento Leve (Zero Tabelas)</h4>
    <p>
        Este plugin utiliza o sistema unificado de <strong>Options</strong> em segundo plano, convertendo os campos criados no Alpine em strings JSON compactas no banco de dados. Isso mantém as estruturas de dados do Lunar Base extremamente enxutas e livres de novas tabelas.
    </p>

    <h4>Como Incorporar no Site</h4>
    <p>Você pode exibir os blocos de perguntas de forma imediata em qualquer local de textos de <strong>Páginas</strong> ou <strong>Posts do Blog</strong> copiando o código abaixo:</p>

    <div class="code">
        [faq slug="seu-slug-aqui"]
    </div>

    <blockquote>
        <strong>Comportamento Responsivo e Acessibilidade:</strong>
        <p>
            A renderização de apresentação utiliza as tags estruturais oficiais do HTML5 (<code>&lt;details&gt;</code> e <code>&lt;summary&gt;</code>). Isso garante que a abertura e o fechamento dos acordeões funcionem de forma ultra-veloz, sem carregar sequer uma única linha de JavaScript no front-end do seu tema público!
        </p>
    </blockquote>
</div>
