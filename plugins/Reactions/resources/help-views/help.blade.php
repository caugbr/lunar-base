<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-thumbs-up class="lucid-icon" />
            Reactions System
        </h3>
        <p>
            Habilita o engajamento de visitantes no seu blog através de reações rápidas positivas e negativas (curtidas/descurtidas) de forma totalmente interativa via AJAX.
        </p>
    </header>

    <h4>Parametrização no Painel Leitura</h4>
    <p>Os controles deste plugin são injetados diretamente na aba de configurações de <strong>Leitura</strong>. O administrador pode definir:</p>
    <ul>
        <li>Se as reações estão ligadas globalmente.</li>
        <li>O ícone das reações (Gostei/Thumbs, Corações ou Estrelas).</li>
        <li>Se reações negativas (Deslikes) são permitidas.</li>
        <li><strong>Reação Única:</strong> Restringe a um voto por IP de visitante (evitando spam de cliques) ou cliques ilimitados.</li>
    </ul>

    <h4>O Ponto de Injeção (Hooks)</h4>
    <p>
        O plugin se conecta ao hook <strong><code>post.meta_end</code></strong> [1] (ou <code>post.after_content</code> dependendo de sua escolha no service provider) para renderizar a barra de reações de forma limpa e automática ao final da leitura.
    </p>

    <h4>Arquitetura Modular e Desacoplada (Plug-and-Play)</h4>
    <p>
        Graças à classe <code>ReactionsHelper</code>, este plugin <strong>não exige nenhuma alteração de código ou inclusão de traits nos seus arquivos de Model do Core</strong>. Qualquer modelo que implemente suporte a metadados no seu Lunar Base CMS passa a ser reativo de forma instantânea e transparente!
    </p>
</div>
