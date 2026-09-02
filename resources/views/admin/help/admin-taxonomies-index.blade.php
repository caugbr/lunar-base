<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Estrutura de Taxonomias
        </h3>
        <p>Gerencie os tipos de agrupamento e classificação disponíveis para os conteúdos do site.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>O que é uma Taxonomia:</strong>
                É um sistema organizador de conteúdo. Exemplos clássicos são <em>Categorias</em> (com hierarquia), <em>Tags</em> (sem hierarquia) ou classificações customizadas como <em>Gêneros</em>, <em>Áreas de Atuação</em> ou <em>Departamentos</em>.
            </li>
            <li>
                <strong>Hierárquica vs. Plana:</strong>
                Indica se a taxonomia permite criar subníveis (árvore de termos, como *Tecnologia → Programação → PHP*) ou se todos os termos ficam no mesmo nível (como tags).
            </li>
            <li>
                <strong>Contagem de Termos:</strong>
                A coluna <em>Termos</em> informa quantos itens existem cadastrados dentro daquela classificação.
            </li>
            <li>
                <strong>Ações:</strong>
                <ul>
                    <li><x-lucide-tags class="lucid-icon" style="width: 14px; height: 14px;" /> <strong>Gerenciar Termos:</strong> Abre a listagem dos termos cadastrados nesta taxonomia.</li>
                    <li><x-lucide-pencil class="lucid-icon" style="width: 14px; height: 14px;" /> <strong>Editar:</strong> Altera nome, regras de seleção e tipos de post associados.</li>
                    <li><x-lucide-trash-2 class="lucid-icon" style="width: 14px; height: 14px;" /> <strong>Excluir:</strong> Remove a taxonomia (atenção: termos vinculados poderão ser impactados).</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <p>
            <x-lucide-info class="lucid-icon" />
            Clique em <strong>Nova Taxonomia</strong> para criar uma nova forma de classificar posts ou páginas.
        </p>
    </div>
</div>
