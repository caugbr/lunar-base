<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-construction class="lucid-icon" />
            Maintenance Mode
        </h3>
        <p>
            Coloca o site público em modo de manutenção ou construção com uma página estilizada e bloqueio inteligente de rotas.
        </p>
    </header>

    <h4>Configurações Administrativas</h4>
    <p>Após ativar o plugin, uma nova aba chamada <strong>Manutenção</strong> aparecerá em <strong>Admin → Configurações</strong>. Por lá, você poderá definir:</p>
    <ul>
        <li>Ativar/Desativar o bloqueio global.</li>
        <li>Ícone em destaque no topo do card</li>
        <li>Título da página de manutenção.</li>
        <li>Mensagem explicativa para os visitantes do site.</li>
        <li>Ícone pequeno no footer</li>
        <li>Texto do footer (padrão "Equipe [site_name]")</li>
    </ul>

    <h4>Whitelist de Segurança e Administração</h4>
    <p>O bloqueio é do tipo <em>Smart-Lock</em>. O sistema de arquivos do plugin garante que:</p>
    <ul>
        <li><strong>Rotas de Sistema fiquem livres:</strong> Caminhos como <code>/admin*</code>, <code>/login*</code>, <code>/logout*</code> e desafiadores de 2FA continuam funcionando normalmente para permitir o acesso da equipe técnica.</li>
        <li><strong>Equipe de Testes livre:</strong> Usuários logados com perfil de <code>admin</code> ou <code>editor</code> conseguem ver e testar o site público normalmente.</li>
    </ul>

    <h4>Importância para o SEO (Status 503)</h4>
    <p>
        Quando o bloqueio está ativo, os visitantes comuns recebem um cabeçalho HTTP de status <strong>503 Service Unavailable</strong>. Isso avisa os robôs de busca (Google, Bing) que o site está passando por ajustes temporários, fazendo com que eles <strong>não indexem</strong> a tela de manutenção no lugar do seu conteúdo real.
    </p>
</div>
