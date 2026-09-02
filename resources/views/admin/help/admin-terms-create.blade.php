<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Criar Novo Termo
        </h3>
        <p>Adicione um novo item de classificação (como uma nova categoria ou tag).</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Taxonomia:</strong>
                Selecione a qual grupo este termo pertence (ex: Categorias, Tags, etc.).
            </li>
            <li>
                <strong>Nome e Slug:</strong>
                O nome é como o termo aparecerá para os visitantes (ex: <em>Notícias Gerais</em>). O slug é o formato usado na URL (ex: <code>noticias-gerais</code>).
            </li>
            <li>
                <strong>Termo Pai (Condicional):</strong>
                Se a taxonomia selecionada for hierárquica, o campo <em>Termo Pai</em> será exibido para que você possa definir este item como uma subcategoria.
            </li>
            <li>
                <strong>Ordem:</strong>
                Defina um valor numérico para ordenar os termos em listas e filtros (0 = padrão/primeiro).
            </li>
            <li>
                <strong>Descrição:</strong>
                Texto explicativo opcional sobre o termo, frequentemente exibido no cabeçalho das páginas de arquivo de categoria.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <p>
            <x-lucide-info class="lucid-icon" />
            Clique em <strong>Salvar</strong> para disponibilizar o termo imediatamente nos formulários de criação de posts e páginas.
        </p>
    </div>
</div>
