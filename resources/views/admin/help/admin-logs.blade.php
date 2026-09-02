<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Logs de Auditoria e Segurança
        </h3>
        <p>Registro cronológico e detalhado de ações executadas pelos usuários no painel administrativo.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Rastreabilidade de Ações:</strong>
                Monitore quem criou, atualizou, excluiu registros ou fez login no sistema, incluindo o carimbo de data/hora exato.
            </li>
            <li>
                <strong>Filtros de Busca:</strong>
                Pesquise rapidamente por termo de ação (ex: <em>login</em>, <em>delete</em>), filtre por nome de usuário ou selecione uma categoria funcional específica.
            </li>
            <li>
                <strong>Metadados Técnicos:</strong>
                Nos registros que contêm detalhes extras, clique no ícone de informação (<x-lucide-info class="lucid-icon" style="width: 14px; height: 14px;" />) para abrir o popup com os dados em JSON (valores antes e depois da alteração, IDs afetados, etc.).
            </li>
            <li>
                <strong>Dados de Conexão:</strong>
                Passe o cursor sobre o nome do usuário para inspecionar o endereço IP de origem e o navegador utilizado na requisição.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Esta tela é estritamente para leitura e auditoria de segurança; os registros não podem ser alterados manualmente pela interface.
            </div>
        </div>
    </div>
</div>
