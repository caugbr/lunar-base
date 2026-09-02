<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Referência de Hooks
        </h3>
        <p>Consulte todos os pontos de injeção e extensão disponíveis nas views do sistema e temas.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Tipos de Hooks:</strong>
                <ul>
                    <li><span class="admin-badge admin-badge-active" style="font-size: 0.75rem;">Ação:</span> Ponto de ancoragem auto-fechado onde plugins podem inserir HTML, scripts ou componentes extras.</li>
                    <li><span class="admin-badge admin-badge-info" style="font-size: 0.75rem;">Filtro:</span> Bloco que possui um conteúdo padrão, mas permite que plugins interceptem ou substituam a marcação.</li>
                </ul>
            </li>
            <li>
                <strong>Filtros por Contexto:</strong>
                Utilize os botões de rádio para alternar a exibição entre hooks exclusivos da <em>Administração</em> (iniciados com <code>admin.</code>) ou do <em>Site Público</em>.
            </li>
            <li>
                <strong>Parâmetros Contextuais:</strong>
                A coluna <em>Parâmetros</em> lista quais variáveis associativas o Blade envia para os callbacks dos plugins atrelados àquele gancho.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Para acoplar código a um hook em seus plugins, utilize a fachada <code>Hook::add('nome.do.hook', callback)</code>.
            </div>
        </div>
    </div>
</div>
