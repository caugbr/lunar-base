<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Criar Nova Taxonomia
        </h3>
        <p>Defina as regras e o comportamento de um novo tipo de agrupamento de conteúdo.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Nome e Slug:</strong>
                O nome identifica a taxonomia no painel (ex: <em>Assuntos</em>). O slug é o identificador único e amigável usado internamente e nas URLs (gerado automaticamente a partir do nome).
            </li>
            <li>
                <strong>Tipos de Publicação Vinculados:</strong>
                Marque onde esta taxonomia deve aparecer (ex: apenas em <em>Posts</em>, apenas em <em>Páginas</em>, ou em ambos). Se nenhuma opção for selecionada, ela estará disponível universalmente para todos os tipos de conteúdo.
            </li>
            <li>
                <strong>Hierárquica (Sim / Não):</strong>
                Se ativado, os termos criados dentro desta taxonomia poderão ter "termos pai" (como Categorias e Subcategorias). Se desativado, funcionará em lista plana (como Tags).
            </li>
            <li>
                <strong>Seleção Única (Única / Múltipla):</strong>
                Define se o autor do post só poderá escolher <strong>um único termo</strong> ao publicar (ex: Categoria Principal) ou se poderá marcar múltiplos termos simultaneamente (ex: várias tags).
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <div>
            <x-lucide-info class="lucid-icon" />
            <div>
                Após salvar a taxonomia, você poderá começar a cadastrar os termos dentro dela.
            </div>
        </div>
    </div>
</div>
