<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Gerenciamento de Plugins
        </h3>
        <p>Controle os módulos adicionais, extensões e integrações ativas no seu sistema.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Ativar e Desativar:</strong>
                Utilize a chave seletora (switch) na coluna de ações para ligar ou desligar um plugin instantaneamente, sem perder configurações salvas.
            </li>
            <li>
                <strong>Ações Globais:</strong>
                No cabeçalho da tabela, você pode usar os botões de atalho para:
                <ul>
                    <li><x-lucide-check-check class="lucid-icon" style="width: 14px; height: 14px;" /> <strong>Ativar todos:</strong> Liga todos os plugins instalados simultaneamente.</li>
                    <li><x-lucide-minus class="lucid-icon" style="width: 14px; height: 14px;" /> <strong>Desativar todos:</strong> Desliga todos os plugins de uma só vez (útil para manutenção ou depuração de erros).</li>
                </ul>
            </li>
            <li>
                <strong>Ajuda Específica do Plugin:</strong>
                Se o plugin possuir documentação interna, um ícone de interrogação (<x-lucide-help-circle class="lucid-icon" style="width: 14px; height: 14px;" />) aparecerá ao lado do nome, abrindo instruções de uso, configurações e shortcodes.
            </li>
            <li>
                <strong>Atualizações Disponíveis:</strong>
                Quando uma nova versão do plugin for publicada no repositório remoto, um aviso amarelo indicará a nova versão com acesso ao <em>Changelog</em> (histórico de novidades/correções) e botão para atualizar em 1 clique.
            </li>
            <li>
                <strong>Localização Física:</strong>
                A coluna <em>Pasta</em> indica o diretório onde o código-fonte do plugin reside (em <code>/plugins</code>).
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Clique em <strong>Instalar plugin</strong> no topo para explorar novos módulos disponíveis no repositório.
            </div>
        </div>
    </div>
</div>
