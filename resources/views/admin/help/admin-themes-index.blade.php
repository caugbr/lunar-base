<div class="help-content">
    <div class="help-header">
        <h3>
            <x-lucide-help-circle class="lucid-icon" />
            Aparência e Temas
        </h3>
        <p>Gerencie o visual, layouts e templates aplicados ao front-end público do seu site.</p>
    </div>

    <div class="help-body">
        <ul class="help-list">
            <li>
                <strong>Como os Temas Funcionam:</strong>
                O Lunar Base possui um layout base nativo, mas um tema pode sobrescrever views específicas (ou o site inteiro), alterando cores, fontes, cabeçalhos, rodapés e estruturas de páginas.
            </li>
            <li>
                <strong>Tema Ativo vs. Inativo:</strong>
                Apenas um tema pode estar ativo por vez. Use a chave seletora (switch) no card do tema para ativá-lo como o visual oficial do site.
            </li>
            <li>
                <strong>Modo de Visualização (Preview Seguro):</strong>
                Nos temas inativos, clique no botão de prévia (<x-lucide-eye class="lucid-icon" style="width: 14px; height: 14px;" />) para abrir o site em uma janela modal com o tema aplicado em tempo real. Isso permite testar e navegar sem que os visitantes do site vejam as alterações.
            </li>
            <li>
                <strong>Atualizações de Temas:</strong>
                Quando houver uma nova versão disponível no repositório remoto, um aviso amarelo indicará o número da versão e um botão para atualizar os arquivos em 1 clique.
            </li>
            <li>
                <strong>Estrutura de Arquivos:</strong>
                Os temas instalados ficam localizados na pasta <code>/themes/{NomeDoTema}</code> na raiz do projeto.
            </li>
        </ul>
    </div>

    <div class="help-footer">
        <p>
            <x-lucide-info class="lucid-icon" />
            Clique em <strong>Instalar tema</strong> no topo para explorar novos modelos e layouts no catálogo remoto.
        </p>
    </div>
</div>
