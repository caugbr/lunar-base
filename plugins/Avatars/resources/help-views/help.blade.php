<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-circle-user class="lucid-icon" />
            Avatars System
        </h3>
        <p>
            Permite que os usuários cadastrados façam o upload de uma imagem personalizada de perfil no formato WebP. O plugin gerencia o ciclo de vida do arquivo de forma autônoma sem alterar as tabelas originais do banco de dados.
        </p>
    </header>

    <h4>Pontos de Injeção (Hooks)</h4>
    <p>O plugin se conecta de forma dinâmica a dois ganchos no painel administrativo do Lunar Base:</p>
    <ul>
        <li><strong><code>admin.header_user_avatar</code>:</strong> Injeta a miniatura circular do avatar do usuário logado no cabeçalho administrativo.</li>
        <li><strong><code>admin.profile_after_card</code>:</strong> Renderiza um card administrativo completo de upload e corte de imagem diretamente na página de Perfil.</li>
    </ul>

    <h4>Como utilizar no Front-End</h4>
    <p>Você pode carregar o avatar de qualquer usuário logado ou visitante do site em qualquer arquivo Blade do seu tema.</p>

    <div class="code">
        &lt;img src="&#123;&#123; \Plugins\Avatars\Helpers\AvatarHelper::getUrl($user) &#125;&#125;" alt="Avatar" class="avatar-circle-sm"&gt;
    </div>

    <h4>Fallback de Visitantes / Gravatar</h4>
    <p>
        O helper <code>AvatarHelper::getUrl()</code> é polimórfico. Ele aceita uma instância de <code>User</code> ou uma string de <code>email</code>. Se o e-mail não pertencer a um usuário logado com foto local, o plugin automaticamente gera o link do <strong>Gravatar</strong> correspondente, sendo ideal para integrar à listagem de comentários.
    </p>

    <h4>Limpeza de Arquivos (Garbage Collector)</h4>
    <p>
        Quando um Administrador exclui um usuário através do painel, o plugin escuta o evento de deleção do Eloquent e remove fisicamente o arquivo <code>{user_id}.webp</code> do disco, prevenindo arquivos órfãos de mídia no servidor.
    </p>
</div>
