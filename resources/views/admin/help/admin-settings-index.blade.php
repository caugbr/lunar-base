<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Configurações Gerais do Sistema
        </h3>
        <p>Central de personalização de parâmetros globais, recursos do Core e extensões do site.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Organização em Grupos e Abas:</strong>
                As opções são divididas por contextos (ex: Geral, Leitura, E-mail, Autenticação, SEO, Navegação). Se habilitado nas preferências, você pode navegar entre eles pelas abas horizontais no topo.
            </li>
            <li>
                <strong>Configurações Injetadas por Plugins:</strong>
                Plugins ativos podem adicionar novos grupos inteiros ou injetar campos personalizados dentro de grupos existentes automaticamente. Se um plugin for desativado, seus campos deixam de aparecer aqui.
            </li>
            <li>
                <strong>Campos Condicionais e Dependências:</strong>
                Algumas opções só são habilitadas quando outro recurso está ligado (ex: configurações de servidor SMTP só ficam editáveis quando o envio de e-mails está ativado).
            </li>
            <li>
                <strong>Campos Especiais e Repetidores:</strong>
                <ul>
                    <li><strong>Switches / Chaves:</strong> Ativam ou desativam funcionalidades instantaneamente.</li>
                    <li><strong>Seletores de Página e Ícones:</strong> Permitem vincular páginas institucionais (como Termos de Uso ou Política de Privacidade) e escolher ícones visuais Lucide.</li>
                    <li><strong>Repetidores (Tabelas dinâmicas):</strong> Permitem adicionar múltiplos itens em lista (ex: domínios extras, links personalizados ou pares de chave/valor).</li>
                </ul>
            </li>
            <li>
                <strong>Gerenciamento de Senhas e Chaves de API:</strong>
                Campos de senha e tokens secretos vêm ocultos por segurança. Para atualizar uma chave/senha, basta digitar o novo valor. Para remover uma chave existente, marque a caixa <em>"Remover senha atual"</em>.
            </li>
            <li>
                <strong>Avisos de Alteração Crítica:</strong>
                Campos que impactam o funcionamento do sistema (como alterar a URL do site ou desativar autenticação) exibirão um diálogo de confirmação ao serem modificados.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <p>
            <x-lucide-info class="lucid-icon" />
            Após realizar qualquer modificação, desça até o final da página e clique em <strong>Salvar Configurações</strong> para aplicar as novas regras.
        </p>
    </div>
</div>
