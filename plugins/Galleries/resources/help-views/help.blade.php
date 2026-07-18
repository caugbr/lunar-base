<div class="plugin-help-content">
    <header>
        <h3><x-lucide-images class="lucid-icon" /> Galleries</h3>
        <p>Exibe galerias de imagens vinculadas ao post/página atual via shortcode.</p>
    </header>

    <h4>Como funciona</h4>
    <p>O plugin lê as imagens já vinculadas ao post (via <code>mediaable</code>) e as renderiza em grid responsivo, com lightbox opcional.</p>

    <h4>Shortcode básico</h4>
    <div class="code">[gallery]</div>
    <p>Exibe todas as imagens vinculadas ao post atual, exceto o thumbnail.</p>

    <h4>Atributos disponíveis</h4>
    <table>
        <thead>
            <tr>
                <th>Atributo</th>
                <th>Valores Aceitos</th>
                <th>Padrão</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>layout</code></td>
                <td><code>grid</code>, <code>masonry</code>, <code>carousel</code></td>
                <td><code>grid</code></td>
                <td>Estilo visual da galeria</td>
            </tr>
            <tr>
                <td><code>ids</code></td>
                <td>IDs separados por vírgula<br>Ex: <code>1,2,3,4</code></td>
                <td>—</td>
                <td>IDs específicos de imagens (ignora o post atual)</td>
            </tr>
            <tr>
                <td><code>columns</code><br><code>cols</code></td>
                <td><code>1</code> a <code>6</code></td>
                <td><code>3</code></td>
                <td>Número de colunas (apenas para layout <code>grid</code>)</td>
            </tr>
            <tr>
                <td><code>size</code></td>
                <td><code>thumb</code>, <code>medium</code>, <code>large</code>, <code>original</code></td>
                <td><code>medium</code></td>
                <td>Tamanho da imagem carregada</td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td>Número inteiro</td>
                <td>—</td>
                <td>Limita a quantidade total de imagens exibidas</td>
            </tr>
            <tr>
                <td><code>orderby</code></td>
                <td><code>id</code>, <code>name</code>, <code>created_at</code></td>
                <td><code>created_at</code></td>
                <td>Campo usado para ordenar as imagens</td>
            </tr>
            <tr>
                <td><code>order</code></td>
                <td><code>asc</code>, <code>desc</code>, <code>rand</code></td>
                <td><code>asc</code></td>
                <td>Direção da ordenação (<code>rand</code> = aleatório)</td>
            </tr>
            <tr>
                <td><code>lightbox</code></td>
                <td><code>true</code>, <code>false</code></td>
                <td><code>true</code></td>
                <td>Ativa o modal de visualização em tela cheia ao clicar</td>
            </tr>
            <tr>
                <td><code>caption</code></td>
                <td><code>true</code>, <code>false</code></td>
                <td><code>true</code></td>
                <td>Exibe o texto alternativo ou legenda ao passar o mouse</td>
            </tr>
            <tr>
                <td><code>gap</code></td>
                <td>Número inteiro (pixels)<br>Ex: <code>8</code>, <code>16</code></td>
                <td><code>8</code></td>
                <td>Espaçamento entre as imagens</td>
            </tr>
            <tr>
                <td><code>ratio</code></td>
                <td><code>square</code>, <code>4/3</code>, <code>16/9</code>, <code>auto</code></td>
                <td><code>square</code></td>
                <td>Proporção das imagens (aspect-ratio)</td>
            </tr>
            <tr>
                <td><code>rounded</code></td>
                <td><code>true</code>, <code>false</code></td>
                <td><code>true</code></td>
                <td>Aplica bordas arredondadas (<code>border-radius</code>) nas imagens</td>
            </tr>
            <tr>
                <td><code>exclude_thumbnail</code></td>
                <td><code>true</code>, <code>false</code></td>
                <td><code>true</code></td>
                <td>Se <code>true</code>, remove a imagem de capa do post da galeria</td>
            </tr>
        </tbody>
    </table>

    <h4>Exemplos de uso</h4>

    <h5>Grid simples (padrão)</h5>
    <div class="code">[gallery]</div>
    <p>Grid 3 colunas, lightbox ativo, formato quadrado.</p>

    <h5>Galeria Masonry (estilo Pinterest)</h5>
    <div class="code">[gallery layout="masonry" columns="3"]</div>
    <p>Imagens com alturas variadas, sem cortes.</p>

    <h5>Carousel/Slider</h5>
    <div class="code">[gallery layout="carousel"]</div>
    <p>Imagens em carrossel horizontal com navegação.</p>

    <h5>IDs específicos</h5>
    <div class="code">[gallery ids="5,8,12,15" ratio="16/9"]</div>
    <p>3 imagens específicas, formato widescreen.</p>

    <h5>Imagens aleatórias</h5>
    <div class="code">[gallery limit="6" order="rand" size="thumb"]</div>
    <p>6 miniaturas aleatórias do post (ótimo para sidebars).</p>

    <h5>Personalização avançada</h5>
    <div class="code">[gallery columns="4" gap="16" rounded="false" caption="false"]</div>
    <p>4 colunas, espaçamento maior, sem bordas arredondadas e sem legenda.</p>

    <h5>Excluindo thumbnail</h5>
    <div class="code">[gallery exclude_thumbnail="false"]</div>
    <p>Inclui a imagem de capa do post na galeria.</p>

    <h4>Descrição dos tamanhos (size)</h4>
    <table>
        <thead>
            <tr>
                <th>Valor</th>
                <th>Uso</th>
                <th>Performance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>thumb</code></td>
                <td>Miniatura 300×300px (cortada)</td>
                <td>Mais leve — ideal para grids densos</td>
            </tr>
            <tr>
                <td><code>medium</code></td>
                <td>Versão redimensionada (800px largura)</td>
                <td>Equilíbrio — padrão recomendado</td>
            </tr>
            <tr>
                <td><code>large</code></td>
                <td>Mesmo que medium (reserva para futuro)</td>
                <td>Equilíbrio</td>
            </tr>
            <tr>
                <td><code>original</code></td>
                <td>Imagem original em alta resolução</td>
                <td>Mais pesada — use apenas se necessário</td>
            </tr>
        </tbody>
    </table>

    <h4>Dicas</h4>
    <ul>
        <li><strong>Use <code>layout="masonry"</code></strong> para imagens com proporções diferentes — evita cortes indesejados.</li>
        <li><strong>Prefira <code>size="medium"</code></strong> para a maioria dos casos — boa qualidade sem pesar o carregamento.</li>
        <li><strong>Ative <code>lightbox="true"</code></strong> para permitir visualização em tela cheia — melhora a experiência do usuário.</li>
        <li><strong>Use <code>order="rand"</code></strong> para criar galerias dinâmicas que mudam a cada carregamento.</li>
        <li><strong>O Populator gera posts com galerias de teste</strong> — use para ver o plugin em ação imediatamente.</li>
    </ul>

    <h4>Menu</h4>
    <p><strong>Configurações → Galleries</strong> (ícone: 🖼️ <code>images</code>)</p>
    <p>Acesso: <code>admin</code> e <code>editor</code>.</p>
</div>
