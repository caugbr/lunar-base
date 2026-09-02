<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Gerenciamento de Usuários
        </h3>
        <p>Controle as contas de administradores, editores e demais perfis de acesso ao painel.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Perfis e Níveis de Acesso:</strong>
                Identifique pelo badge o nível do usuário (ex: <span class="admin-badge admin-badge-admin">Admin</span> com controle total, ou <span class="admin-badge admin-badge-editor">Editor</span> com permissões restritas a publicações).
            </li>
            <li>
                <strong>Autenticação de Dois Fatores (2FA):</strong>
                Usuários com proteção 2FA ativa exibem um botão com escudo (<x-lucide-shield-off class="lucid-icon" style="width: 14px; height: 14px;" />). O Administrador pode desativar o 2FA de um usuário caso ele tenha perdido o acesso ao aplicativo autenticador.
            </li>
            <li>
                <strong>Segurança de Conta Própria:</strong>
                O sistema impede a exclusão acidental da própria conta logada no momento.
            </li>
            <li>
                <strong>Ações:</strong>
                Use o botão de lápis (<x-lucide-pencil class="lucid-icon" style="width: 14px; height: 14px;" />) para editar dados ou a lixeira (<x-lucide-trash-2 class="lucid-icon" style="width: 14px; height: 14px;" />) para revogar o acesso removendo o usuário.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Clique em <strong>Novo Usuário</strong> no topo para cadastrar um novo membro na equipe.
            </div>
        </div>
    </div>
</div>
