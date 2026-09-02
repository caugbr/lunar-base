<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Transferência de Conteúdo
        </h3>
        <p>Mova conteúdos completos entre ambientes (ex: desenvolvimento, homologação e produção) via arquivos JSON.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Exportar Dados:</strong>
                Selecione quais tipos de conteúdo deseja incluir no arquivo (Taxonomias/Termos, Posts, Páginas) e clique em <em>Gerar Arquivo de Exportação</em> para baixar um arquivo <code>.json</code> completo.
            </li>
            <li>
                <strong>Importar Dados:</strong>
                Faça o upload de um arquivo <code>.json</code> gerado previamente pelo Lunar Base.
            </li>
            <li>
                <strong>Estratégias para Duplicatas (Slugs iguais):</strong>
                <ul>
                    <li><strong>Ignorar (Padrão):</strong> Se o slug da página/post já existir no banco, ele pula e mantém o conteúdo atual intacto.</li>
                    <li><strong>Sobrescrever:</strong> Atualiza os textos, títulos e metadados do conteúdo existente com as informações do arquivo importado.</li>
                    <li><strong>Importar como Novo Rascunho:</strong> Cria uma nova cópia do conteúdo com status de Rascunho e adiciona um sufixo numérico no slug (ex: <code>minha-pagina-1</code>).</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <p>
            <x-lucide-alert-triangle class="lucid-icon" style="color: #f59e0b;" />
            Recomenda-se realizar um backup prévio do banco de dados antes de executar importações em massa em ambiente de produção.
        </p>
    </div>
</div>
