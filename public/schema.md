## 1. Visão Geral da Arquitetura

O **Lunar Base** é uma aplicação híbrida de gerenciamento de conteúdo (CMS) e Starter Kit modular desenvolvida sobre o framework Laravel. O sistema foi projetado para operar com foco em performance e extensibilidade, utilizando uma arquitetura baseada em arquivos de configuração locais (no diretório `/config`) e um mecanismo dinâmico de carregamento de extensões em `/plugins` e temas em `/themes`. 

A estrutura conta com um orquestrador centralizado de rotas públicas para a resolução de caminhos amigáveis e flexíveis, um barramento integrado de *Hooks* (pontos de injeção visual) e suporte a *Shortcodes* para componentes de apresentação.

---

## 2. Modelagem de Dados (Banco de Dados & Models)

### Tabelas e Estruturas de Migrations (Database)

*   **`users`**: Armazena registros de usuários, credenciais criptografadas e atribuições de perfis estáticos (`role`).
*   **`admin_logs`**: Registra dados de auditoria detalhados (endereço IP, agente de usuário, referência, categoria e metadados estruturados em JSON).
*   **`media`**: Gerencia arquivos em um modelo polimórfico, armazenando caminhos físicos, dimensões físicas (`width` e `height`), metadados alternativos (`alt`), legendas (`caption`) e campos JSON flexíveis (`meta`).
*   **`pages`**: Estrutura páginas institucionais com suporte hierárquico (`parent_id`), seleção de layouts (`template`), chaves de controle de URL (`namespace` e `slug`) e status de publicação.
*   **`posts`**: Armazena artigos dinâmicos para a listagem cronológica do blog com agendamento temporal de publicação (`published_at`) e controle de posicionamento (`featured` e `sticky`).
*   **`post_meta`**: Armazena pares de chave-valor arbitrários associados a posts (esquema clássico de metadados estendidos).
*   **`taxonomies`**: Define agrupamentos conceituais para classificação (ex: categorias, tags, etc.) com propriedade de herança (`hierarchical`).
*   **`terms`**: Registros de itens pertencentes a uma taxonomia, com encadeamento hierárquico (`parent_id`).
*   **`term_relationships`**: Tabela associativa polimórfica que vincula termos (`terms`) a modelos como `Page` e `Post`.
*   **`two_factor_settings`**: Armazena as propriedades de autenticação em duas etapas por usuário, incluindo segredos TOTP (`secret`) criptografados e chaves dinâmicas descartáveis por e-mail (`otp_code`).
*   **`plugins`**: Catálogo de módulos adicionais instalados fisicamente, guardando o nome da classe principal do fornecedor do serviço (`service_provider_class`).
*   **`themes`**: Registro de diretórios de temas ativos na aplicação.

#### Tabelas de Plugins Integrados
*   **`comments`** (Plugin `Comments`): Registro de mensagens públicas com auto-relacionamento (`parent_id`) e vínculos a entidades polimórficas.
*   **`forms`** (Plugin `Forms`): Definições de formulários com um esquema dinâmico de campos serializado em JSON (`fields_schema`).
*   **`form_submissions`** (Plugin `Forms`): Registros de envios de formulários armazenados em JSON (`data`).
*   **`maps`** (Plugin `Maps`): Configurações de exibição de mapas interativos, coordenadas centrais e propriedades de polígonos GeoJSON.
*   **`map_markers`** (Plugin `Maps`): Marcadores individuais vinculados a um mapa, com suporte a metadados flexíveis (`parameters`).
*   **`menus`** (Plugin `Menus`): Cadastra recipientes de menus e associações físicas de posicionamento (`hook`).
*   **`menu_items`** (Plugin `Menus`): Estrutura de links ordenada, com encadeamento pai-filho e mapeamento polimórfico a entidades internas ou links externos customizados.

---

### Models do Laravel e Relacionamentos Eloquent

*   **`User`**
    *   `hasOne(TwoFactorSetting::class)` — Configurações de autenticação de duas etapas.
*   **`AdminLog`**
    *   `belongsTo(User::class, 'user_id')` — Autor associado ao registro de log.
*   **`Media`**
    *   `morphTo()` — Associação polimórfica genérica (`mediaable`).
*   **`Page`**
    *   `belongsTo(Page::class, 'parent_id')` — Página hierárquica superior.
    *   `hasMany(Page::class, 'parent_id')` — Subpáginas filhas associadas.
    *   `belongsTo(User::class, 'author_id')` — Administrador ou editor criador.
    *   `belongsTo(Media::class, 'thumbnail_id')` — Imagem de destaque principal.
    *   `morphMany(Media::class, 'mediaable')` — Galeria de mídias associadas.
    *   `morphToMany(Term::class, 'termable', 'term_relationships')` — Termos de taxonomia atribuídos.
*   **`Post`**
    *   `belongsTo(User::class, 'author_id')` — Autor da publicação.
    *   `belongsTo(Media::class, 'thumbnail_id')` — Imagem de destaque do artigo.
    *   `morphMany(Media::class, 'mediaable')` — Galeria de imagens do artigo.
    *   `morphToMany(Term::class, 'termable', 'term_relationships')` — Termos classificatórios associados.
    *   `hasMany(PostMeta::class, 'post_id')` — Metadados customizados vinculados.
*   **`PostMeta`**
    *   `belongsTo(Post::class, 'post_id')` — Artigo pai do metadado.
*   **`Taxonomy`**
    *   `hasMany(Term::class)` — Termos integrantes do grupo de classificação.
*   **`Term`**
    *   `belongsTo(Taxonomy::class)` — Taxonomia proprietária do termo.
    *   `belongsTo(Term::class, 'parent_id')` — Termo pai imediato.
    *   `hasMany(Term::class, 'parent_id')` — Subtermos filhos vinculados.
    *   `morphedByMany(Page::class, 'termable', 'term_relationships')` — Páginas classificadas pelo termo.
    *   `morphedByMany(Post::class, 'termable', 'term_relationships')` — Posts classificados pelo termo.
*   **`TwoFactorSetting`**
    *   `belongsTo(User::class)` — Usuário proprietário do 2FA.

#### Models de Plugins
*   **`Comment`** (Plugin `Comments`)
    *   `morphTo()` — Modelo de conteúdo comentado (`commentable`).
    *   `belongsTo(User::class)` — Autor registrado associado.
    *   `belongsTo(Comment::class, 'parent_id')` — Comentário pai em respostas aninhadas.
    *   `hasMany(Comment::class, 'parent_id')` — Lista de respostas do comentário.
*   **`Form`** (Plugin `Forms`)
    *   `hasMany(FormSubmission::class, 'form_id')` — Envios recebidos.
*   **`FormSubmission`** (Plugin `Forms`)
    *   `belongsTo(Form::class, 'form_id')` — Formulário pai da resposta.
*   **`Map`** (Plugin `Maps`)
    *   `hasMany(MapMarker::class)` — Marcadores geográficos vinculados.
*   **`MapMarker`** (Plugin `Maps`)
    *   `belongsTo(Map::class)` — Mapa pai do marcador.
*   **`Menu`** (Plugin `Menus`)
    *   `hasMany(MenuItem::class, 'menu_id')` — Itens que compõem o menu.
    *   `hasMany(MenuItem::class, 'menu_id')->whereNull('parent_id')` — Itens no nível raiz.
*   **`MenuItem`** (Plugin `Menus`)
    *   `belongsTo(Menu::class, 'menu_id')` — Menu associado.
    *   `belongsTo(MenuItem::class, 'parent_id')` — Item pai hierárquico.
    *   `hasMany(MenuItem::class, 'parent_id')` — Subitens aninhados.
    *   `morphTo()` — Resolução polimórfica para obter rotas de destino (`model`).

---

## 3. Funcionalidades do Painel Administrativo (Back-end)

### Proteção de Rotas, Middlewares e Controle de Acesso (RBAC)

A área administrativa é prefixada sob o caminho `/admin`. A segurança de acesso é tratada de forma estática com perfis descritos em `config/rolesPermissions.php` e validada por meio de aliases mapeados em `bootstrap/app.php`:

*   `auth`: Middleware de autenticação nativo para restrição de convidados.
*   `role:admin,editor`: Restrição com base em múltiplos papéis acumulados de forma explícita.
*   `permission:manage-pages` / `permission:manage-posts` / `permission:manage-settings`: Restringe o acesso a recursos específicos baseando-se nas chaves lógicas de permissão do usuário ativo.

---

### Mapeamento dos Controladores e CRUDs Administrativos

As rotas administrativas estão registradas em `routes/admin.php` e complementadas pelas rotas registradas nos arquivos `routes.php` de cada plugin ativo:

#### Controladores do Core
*   **`AuthController`**
    *   `GET /login` (`login`) — Exibição do formulário de acesso administrativo.
    *   `POST /login` — Processamento de credenciais e redirecionamento.
    *   `POST /logout` (`logout`) — Encerramento de sessão ativa.
*   **`DashboardController`**
    *   `GET /admin/dashboard` (`admin.dashboard.index`) — Carregamento do dashboard unificado de widgets dinâmicos.
*   **`UserController`** *(Apenas perfil `admin`)*
    *   `Resource /admin/users` — CRUD completo para administração de usuários e atribuição de perfis de sistema.
*   **`ProfileController`**
    *   `GET /admin/profile` (`admin.profile.edit`) — Tela para alteração de dados do usuário logado e gerenciamento de 2FA.
    *   `PUT /admin/profile` (`admin.profile.update`) — Persistência dos dados cadastrais e senha pessoal.
*   **`PageController`** *(Requer permissão `manage-pages`)*
    *   `Resource /admin/pages` — CRUD de páginas do site, com seletor de templates de visualização, taxonomia e hierarquia.
*   **`PostController`** *(Requer permissão `manage-posts`)*
    *   `Resource /admin/posts` — CRUD de artigos do blog, incluindo agendamento temporal de postagem, fixação e metadados customizados.
*   **`MediaController`** *(Requer permissão `manage-media`)*
    *   `GET /admin/media/data` (`admin.media.data`) — Endpoint assíncrono (JSON paginado) utilizado pelo modal de mídia no front-end.
    *   `Resource /admin/media` (exceto `create`, `show`) — Gerenciador físico e lógico de mídias, com redimensionamento dinâmico em segundo plano de variações (`thumb` e `large`).
*   **`TaxonomyController`**
    *   `Resource /admin/taxonomies` — Gerencia classificações globais (ex: tags e categorias).
*   **`TermController`**
    *   `Resource /admin/terms` — Gerencia os termos e heranças de termos filhos pertencentes a uma taxonomia.
*   **`SettingController`** *(Apenas perfil `admin`)*
    *   `GET /admin/settings` (`admin.settings.index`) — Interface de parametrização global do sistema e de plugins de forma reativa ou segmentada.
    *   `POST /admin/settings` (`admin.settings.update`) — Validação dinâmica e armazenamento tipado de opções na tabela de configurações.
*   **`AdminLogController`** *(Apenas perfil `admin`)*
    *   `GET /admin/logs` (`admin.logs.index`) — Filtro de buscas e visualização de trilhas de auditoria administrativa.
*   **`PluginController`** *(Apenas perfil `admin`)*
    *   `GET /admin/plugins` (`admin.plugins.index`) — Sincronizador de diretórios e ativador individual de extensões.
    *   `POST /admin/plugins/{plugin}/toggle` (`admin.plugins.toggle`) — Altera o estado lógico de ativação de um plugin.
    *   `POST /admin/plugins/toggle-all/{status}` (`admin.plugins.toggle_all`) — Ativação ou desativação em lote de todas as extensões do sistema.
*   **`ThemeController`** *(Apenas perfil `admin`)*
    *   `GET /admin/themes` (`admin.themes.index`) — Exibição de catálogo de temas instalados fisicamente.
    *   `POST /admin/themes/{theme}/toggle` (`admin.themes.toggle`) — Ativação ou desativação lógica de temas.
    *   `GET /admin/themes/{theme}/screenshot` (`admin.themes.screenshot`) — Carregador seguro de imagem de visualização do tema ativo.
*   **`TwoFactorManagementController`** *(Apenas perfil `admin`)*
    *   `DELETE /admin/users/{user}/two-factor` (`admin.users.two-factor.disable`) — Remoção manual e administrativa do segundo fator de autenticação de um usuário.

#### Controladores de Plugins (Administração)
*   **`AvatarController`** (Plugin `Avatars`)
    *   `POST /admin/profile/avatar` (`admin.profile.avatar.update`) — Processamento físico, recorte 150x150 e conversão do avatar do usuário para WebP.
    *   `DELETE /admin/profile/avatar` (`admin.profile.avatar.destroy`) — Exclusão do avatar ativo e retorno ao fallback correspondente.
*   **`FAQController`** (Plugin `FAQ`, `role:admin,editor`)
    *   `Resource /admin/faq` — Permite criar e atualizar coleções de perguntas e respostas gravadas como strings JSON em opções de banco.
*   **`FormsController`** (Plugin `Forms`, `role:admin,editor`)
    *   `Resource /admin/forms` — Construtor interativo de formulários dinâmicos com definição de regras de validação nativas do Laravel.
*   **`FormSubmissionController`** (Plugin `Forms`, `role:admin,editor`)
    *   `Resource /admin/forms/{form}/submissions` (apenas `index`, `show`, `destroy`) — Leitor de envios recebidos e visualização de metadados em grade detalhada.
*   **`MapController`** (Plugin `Maps`, `role:admin,editor`)
    *   `Resource /admin/maps` — Construtor e gerenciador de mapas do OpenStreetMap, permitindo adicionar, importar (JSON) e estilizar pinos e limites territoriais.
*   **`MenuController`** (Plugin `Menus`, `role:admin,editor`)
    *   `Resource /admin/menus` — Tela para criação e configuração de menus lógicos.
    *   `POST /admin/menus/{menu}/save-items` (`admin.menus.save_items`) — Endpoint assíncrono para persistência recursiva estruturada em árvore da hierarquia dos links configurada no construtor.

---

## 4. Funcionalidades Públicas (Front-end)

### Orquestrador de Rotas Públicas e Resolução de URL Amigável

O front-end utiliza o controlador **`RouteOrchestratorController`** para receber e processar as requisições públicas de acordo com o padrão e número de segmentos de URL, mapeados no final do arquivo `routes/web.php`:

*   **Segmento Único (`/{base}`)**:
    *   Resolve para o blog principal se `base` corresponder à configuração de base de URL de blog (padrão: `/blog`).
    *   Resolve para exibição de uma página institucional direta se houver correspondência com o campo `slug` (sem `namespace` definido).
    *   Verifica correspondência de rota customizada registrada por plugins no resolvedor `DynamicRoutes::resolve($base)`.
*   **Dois Segmentos (`/{base}/{slug}`)**:
    *   Resolve para páginas institucionais com prefixo estático (ex: `/pagina/sobre-nos`).
    *   Resolve para posts individuais sob a base do blog (ex: `/blog/titulo-da-noticia`).
    *   Resolve para páginas que utilizem `namespace` (ex: `/institucional/missao`) quando a configuração de base de páginas está desativada.
*   **Três Segmentos (`/{base}/{namespace}/{slug}`)**:
    *   Resolve para páginas com namespace sob um prefixo estático configurado (ex: `/pagina/institucional/missao`).
    *   Resolve para listagens e buscas do blog filtradas por termos de taxonomia (ex: `/blog/categoria/tutoriais`).
*   **Captura Genérica (`/{any}`)**:
    *   Verifica registros manuais de rotas dinâmicas cadastrados por plugins ativos ou retorna erro HTTP 404 (Página não encontrada).

---

### Rotas Públicas e Endpoints Funcionais

*   **`HomeController`**
    *   `GET /` (`home`) — Renderização da página inicial pública da aplicação.
*   **`TwoFactorChallengeController`**
    *   `GET /two-factor/challenge` (`two-factor.challenge`) — Exibição da tela de desafio de segundo fator de autenticação.
    *   `POST /two-factor/challenge` — Validação da autenticação via TOTP ou código dinâmico por e-mail.
    *   `POST /two-factor/send-email` (`two-factor.send-email`) — Dispara nova chave dinâmica descartável para o endereço de e-mail do usuário.
*   **`TwoFactorSetupController`**
    *   `POST /two-factor/setup-email-trigger` (`two-factor.setup-email-trigger`) — Envio de código dinâmico por e-mail no processo de ativação da conta.
    *   `DELETE /two-factor/setup` (`two-factor.cancel`) — Aborta o processo de setup e remove as configurações provisórias geradas.

#### Endpoints Públicos de Plugins
*   **`CommentController`** (Plugin `Comments`)
    *   `POST /comments` (`comments.store`) — Processamento e validação de novos comentários enviados por visitantes ou usuários, com suporte a aninhamento em árvore e filtro simples de moderação de spam.
*   **`GenericFormController`** (Plugin `Forms`)
    *   `GET /formulario/{slug}` (`public.forms.show`) — Exibe o formulário dinâmico em página exclusiva.
    *   `POST /formulario/{slug}` (`public.forms.submit`) — Valida os dados de envio em tempo de execução com base no esquema configurado, armazena no banco de dados e dispara notificações SMTP se ativado.
*   **`ReactionController`** (Plugin `Reactions`)
    *   `POST /react/{type}/{id}/{value}` (`react`) — Adiciona ou altera reações (likes/dislikes) em artigos ou páginas dinâmicas, com controle anti-fraude por hash de IP.
*   **`GeoJsonController`** (Plugin `Maps`)
    *   `GET /api/maps/public/geojson/{pid}` (`api.maps.public.geojson.show`) — Endpoint que devolve a coleção de limites geográficos GeoJSON salvos para renderização em mapas públicos.

---

### Apresentação, Embeds e Shortcodes

A renderização visual é realizada através de views do Laravel estruturadas em temas com componentes integrados ao ecossistema Blade. O sistema conta com os seguintes shortcodes utilizáveis em campos de editores de texto:

*   **`[form slug="seu-slug"]`**: Insere e renderiza o layout e a validação do formulário correspondente de maneira nativa dentro do conteúdo de páginas e posts.
*   **`[faq slug="seu-slug"]`**: Exibe sanfonas e acordeões de perguntas e respostas com tags puras HTML5 (`<details>` e `<summary>`), oferecendo alta velocidade de processamento por não exigir arquivos JS adicionais no front-end.
*   **`[map id="X"]`** ou **`[map slug="seu-slug"]`**: Incorpora mapas interativos interligados ao Leaflet.js, renderizando marcadores, polígonos GeoJSON de limites territoriais e popups informativos.
*   **`[list-locations id="X"]`**: Renderiza a listagem de locais cadastrados em um mapa no formato de cards públicos interativos.
*   **`[subpages]`**: Mapeia a hierarquia de páginas e renderiza de forma automática uma seção de links de navegação para as subpáginas filhas da página ativa.
*   **`[script]`, `[style]`, `[link]`**: Permitem a inserção isolada de assets CSS e JS de forma controlada através do utilitário `@onceAsset`, impedindo carregamentos duplicados ao repetir componentes de plugins na mesma página.
