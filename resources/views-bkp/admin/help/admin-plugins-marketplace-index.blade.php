<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Repositório de Plugins
        </h3>
        <p>Explore, baixe, instale ou remova extensões e módulos oficiais para o Lunar Base.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Instalação em Lote:</strong>
                Marque a caixa de seleção dos plugins que deseja adicionar e clique no botão <strong>Instalar Selecionados</strong> no topo da página. O sistema fará o download e a extração automática.
            </li>
            <li>
                <strong>Status dos Plugins:</strong>
                <ul>
                    <li><span class="admin-badge admin-badge-active" style="font-size: 0.75rem;">Ativo:</span> O plugin está instalado e em execução no sistema.</li>
                    <li><span class="admin-badge admin-badge-suspended" style="font-size: 0.75rem;">Instalado:</span> O plugin está baixado no servidor, mas desligado.</li>
                    <li><span class="admin-badge" style="background-color: #e5e7eb; color: #4b5563; font-size: 0.75rem;">Não Instalado:</span> Disponível para download direto do repositório.</li>
                </ul>
            </li>
            <li>
                <strong>Remover Plugins:</strong>
                Plugins que estão instalados (mas inativos) exibem um ícone de lixeira (<x-lucide-trash-2 class="lucid-icon" style="width: 14px; height: 14px;" />) para exclusão completa dos arquivos do servidor.
            </li>
            <li>
                <strong>Sincronizar Catálogo:</strong>
                Use o botão <strong>Atualizar</strong> (<x-lucide-refresh-cw class="lucid-icon" style="width: 14px; height: 14px;" />) para forçar uma nova consulta ao repositório remoto e verificar se há novos plugins ou versões recém-lançadas.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Após instalar um plugin, volte para a tela de <strong>Plugins Instalados</strong> para ativá-lo e configurá-lo.
            </div>
        </div>
    </div>
</div>
