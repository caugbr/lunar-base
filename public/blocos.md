
# Especificação Arquitetural: Sistema de Componentes Desacoplados e Protegidos para CMS

**Projeto Base:** Laravel (Backend/CMS) + Vue 3 / Tiptap 3.x (Frontend/Editor)  
**Objetivo de Negócio:** Permitir a reutilização de estruturas visuais complexas, oferecendo autonomia total de conteúdo aos clientes, garantindo **impossibilidade de quebra de layout/design**.

---

## 1. O Problema de Negócio (A Dor do Cliente e do Desenvolvedor)

Em CMSs tradicionais baseados em editores ricos (como WordPress clássico ou implementações padrão do Tiptap), o editor gera HTML cru misturado diretamente com o texto digitado pelo cliente. 

Isso gera três problemas críticos:
1. **Quebra de Design:** Clientes acidentalmente apagam tags de fechamento (`</div>`), deletam classes essenciais de grid responsivo ou colam estilos sujos (ex: vindos do Microsoft Word).
2. **Impossibilidade de Atualização em Massa:** Se o layout de um "Card de Depoimento" precisar de um novo visual no tema meses depois, o desenvolvedor precisa editar centenas de posts manualmente ou rodar queries arriscadas no banco.
3. **Falta de Abstração:** O cliente é forçado a gerenciar estética quando deveria focar apenas na mensagem e no conteúdo.

---

## 2. A Filosofia: Desacoplamento entre Molde (Estrutura) e Conteúdo (Dados)

A solução baseia-se em dividir a funcionalidade em dois ambientes totalmente isolados:

1. **O Fabricante (Blueprint / Molde):** Onde o desenvolvedor/admin cria o layout HTML/CSS visual puro e demarca onde ficam os "buracos" preenchíveis (slots).
2. **O Consumidor (Instance / Instância):** Onde o cliente final insere o bloco na página e digita apenas as informações necessárias para preencher os buracos, sem acesso ao código-fonte ou às tags do container.

---

## 3. A Mecânica de Mapeamento: O Atributo `data-var`

Em vez de usar substituição de texto com chaves (ex: `{{titulo}}`), que clientes podem apagar ou digitar errado, a marcação é feita a nível de **árvore DOM/Nós do Tiptap** usando atributos semânticos: `data-var="identificador"`.

* **Vantagem sobre tags de template:** Funciona nativamente em elementos que não contêm texto, como tags `<img>` e links `<a>`.
* **Exemplo de Molde gerado pelo Tiptap:**
  ```html
  <div class="lunar-card-profile" data-component="card-perfil">
    <img src="/placeholder.jpg" data-var="foto_autor" alt="Foto">
    <h3 data-var="nome_autor">Nome do Especialista</h3>
    <p data-var="biografia">Texto breve sobre a pessoa...</p>
    <a href="#" data-var="link_linkedin">Perfil Profissional</a>
  </div>
  ```

---

## 4. O Construtor de Componentes (Ambiente de Fabricação)

Uma tela de CRUD dedicada no Laravel exclusiva para a criação de moldes, utilizando uma instância do Tiptap configurada para **Modo Fabricação**.

### A. O "Modo de Inspeção" (Inspector Mode)
* O desenvolvedor monta o layout visual usando os blocos nativos já existentes (Colunas, Cards, Banners, Imagens).
* Clica no botão **"Mapear Variáveis"** na Toolbar.
* O editor entra em estado suspenso (`editable: false`) e ativa ouvintes de `mouseover`:
  * Elementos elegíveis sob o cursor ganham destaque visual (borda azul pontilhada, cursor em cruz).
  * Elementos já mapeados exibem uma tag fixa de feedback (ex: `[nome_autor]`).

### B. A Regra de Segurança: "Apenas Nós Folha" (Leaf Nodes)
Para garantir que a substituição de dados nunca destrua containers ou grids, o seletor **ignora tags estruturais pai** (`div`, `section`, `article`) e só permite clique em:
1. **Blocos de texto simples:** `<p>`, `<h1>`-`<h6>`, `<blockquote>`, `<li>`.
2. **Elementos vazios de mídia:** `<img>`, `<video>`.

### C. Definição do Slot
Ao clicar no elemento válido, abre-se um balão ancorado (Popover) onde o desenvolvedor define a chave da variável (ex: `subtitulo_home`). O editor injeta `data-var="subtitulo_home"` naquele nó específico.

---

## 5. O Consumo na Edição de Páginas (Ambiente do Cliente)

Quando o cliente está criando ou editando um Artigo/Página regular:

1. **A Toolbar é limpa:** As ferramentas de "Mapear Variáveis" **não existem** neste contexto (definidas via `tools` na configuração do Blade).
2. **Inserção do Bloco:** No menu de blocos, o componente aparece com nome e miniatura visual.
3. **Preenchimento sem Risco:** 
   * O bloco é renderizado no Tiptap como um componente atômico selado (`atom: true`).
   * Os nós marcados com `data-var` viram campos interativos com foco de edição, ou abrem uma aba lateral com os campos: *Foto*, *Nome*, *Biografia*.
   * O cliente **não consegue deletar a div**, mudar a margem, alterar o grid ou quebrar o CSS.

---

## 6. Persistência de Dados e Compilação no Laravel (Dual Storage)

O sistema opera com o conceito de **Early Binding (Compilação ao Salvar)**:

### O que o Banco armazena:
* **Tabela `blueprints` (Molde):**
  * `structure_json`: O documento Tiptap da casca.
  * `template_html`: O HTML base contendo os atributos `data-var`.
  * `thumbnail_url`: Imagem de preview gerada automaticamente.
* **Tabela `pages` / `posts` (Consumo):**
  * `content_json`: O documento original do editor (para reabrir a edição no futuro).
  * `content_html`: O **HTML estático final** já pronto para o visitante do site.

### O Fluxo no Laravel ao Salvar a Página:
1. O controller do Laravel recebe o conteúdo.
2. Onde houver uma instância de componente, o PHP busca o HTML do molde referenciado.
3. Utilizando o manipulador nativo de DOM do PHP (`DOMDocument` / `Symfony Crawler`), o Laravel busca os seletores `[data-var]`:
   * Textos: Substitui o conteúdo interno (`nodeValue`).
   * Imagens: Injeta o novo `src` e `alt`.
   * Links: Injeta o novo `href`.
4. Os atributos `data-var` são removidos para limpar o HTML final.
5. O HTML resultante é salvo na coluna `content_html` da página.
6. **Performance pública:** O visitante recebe HTML puro direto do banco, com custo computacional zero em tempo de requisição.

---

## 7. Catálogo Visual: Geração Automática de Thumbnails

Para proporcionar uma experiência de software premium (nível Webflow/Framer), os blocos contam com miniaturas automáticas no catálogo de inserção.

* **No momento de salvar o Molde:** Antes de enviar o formulário, o JavaScript utiliza uma biblioteca de canvas leve (como `html-to-image`) apontada para a div de visualização do editor.
* O print é convertido em WebP e enviado via payload em Base64 para o Laravel.
* O Laravel armazena o arquivo no Storage e vincula a URL àquele Blueprint.
* **Resultado na UX:** No menu de blocos das páginas, o cliente não escolhe a partir de uma lista cega de textos, mas navega por uma **galeria visual de cartões** com previews reais de cada layout.

---

## 8. Estratégia Pragmática de Implementação (Roadmap)

### Fase 1: Atual (Pronto para Clientes)
* Usar a excelente base já construída em Vue 3 / Tiptap (`CardBlock`, `MediaTextBlock`, `CoverBlock`, etc.).
* Os blocos são pré-definidos pelo desenvolvedor via código (garantindo que o cliente não quebre nada hoje).
* O editor já faz dual-storage (`content_json` e `content_html`) e sincronização em tempo real.

### Fase 2: Futura (Escala / White-label / SaaS)
* Criar a rota e controller dedicado para o CRUD de Blueprints no Laravel.
* Adicionar a extensão de inspeção no Tiptap que injeta o atributo global `data-var`.
* Adicionar o parser de compilação de HTML via `DOMDocument` no Laravel.
* Implementar a captura de thumbnail via canvas para o catálogo de inserção de blocos.
