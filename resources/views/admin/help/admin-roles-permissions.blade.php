<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Esquema de Roles e Permissões
        </h3>
        <p>Mapa de controle de acesso e matriz de privilégios dos perfis de usuário do sistema.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Perfis de Acesso (Roles):</strong>
                Os cards no topo resumem cada papel existente no sistema, exibindo a quantidade de permissões ativas e uma breve descrição do seu propósito.
            </li>
            <li>
                <strong>Matriz de Permissões:</strong>
                A tabela comparativa cruza cada ação do sistema (separada por grupos funcionais) com os perfis cadastrados, indicando com um visto (<x-lucide-check class="lucid-icon" style="color: #22c55e; width: 14px; height: 14px;" />) quem possui autorização para executá-la.
            </li>
            <li>
                <strong>Definição e Customização:</strong>
                Toda a matriz e os grupos de permissões são estruturados e mantidos no arquivo de configuração do sistema.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Para adicionar novas permissões ou alterar os privilégios dos perfis, edite o arquivo <code>config/rolesPermissions.php</code>.
            </div>
        </div>
    </div>
</div>
