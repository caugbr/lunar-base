# Lunar Base - Mapa Arquitetural do Sistema

## Comandos Artisan
- FakeMigrate - registra as migrations sem executar
- LinkPluginAssets - cria o link simbólico para um plugin (--unlink para desvincular)
- LinkThemeAssets - cria o link simbólico para um tema (--unlink para desvincular)
- PluginCreate - cria a base para um novo plugin com seleção de tags
- PluginEditTags - define/altera interativamente as tags de um plugin
- QuickCreateUser - cria um usuário administrativo via terminal
- ThemeCreate - cria a base para um novo tema com seleção de tags
- ThemeEditTags - define/altera interativamente as tags de um tema
- UpdateTutorials - atualiza versão e data nos tutoriais

## Helper Functions

### Versão
- appVersion - retorna a versão atual do sistema

### Assets
- add_script - enfileira um script sem duplicação
- add_inline_script - enfileira um script inline
- add_style - enfileira um estilo sem duplicação
- add_inline_style - enfileira um estilo inline

### Database
- dbAvailable - checa se a database é acessível ou se uma tabela existe

### Hooks no PHP
- add_action - adiciona uma action
- do_action - dispara uma action no código
- add_filter - adiciona um filtro a uma variável
- apply_filters - aplica os filtros registrados a uma variável

### Hooks nas views
- hook - renderiza um hook Blade
- get_discovered_hooks - lista todos os hooks encontrados no código
- render_hooks_select - renderiza um select com todos os hooks disponíveis

### Imagens
- uploadImage - processa o upload de uma imagem
- deleteImage - remove uma imagem e suas variações
- getImage - retorna a URL de uma imagem
- generateMediaVariants - cria as variações otimizadas para uma imagem (chamada em uploadImage)
- deleteMediaVariants - remove as variações de uma imagem (chamada em deleteImage)

### Log
- log_admin - registra uma ação na tabela admin_logs com categoria e metadados

### Options
- getOption - retorna o valor de uma option tipada
- getPrefixedOptions - retorna um array de options com o mesmo prefixo
- setOption - define uma option com suporte a casting para JSON ou criptografia (password)

### Settings
- getSettingsDefinitions - retorna todas as definições de settings (core + plugins injetados)
- settingDefault - retorna o valor padrão (default) de uma configuração
- setting - retorna o valor salvo de uma configuração
- settingsGroup - retorna um array com todos os itens de um grupo
- settingsAll - retorna todas as configurações cadastradas

### Roles / Permissions
- isRole - checa se o usuário atual possui a role informada
- userCan - checa se o usuário atual possui a permission informada

### Gerenciamento de Subdomínios
- currentSiteData - retorna os dados de mapeamento do domínio atual
- currentSiteDomain - retorna o domínio/subdomínio atual como string
- currentNamespace - retorna apenas a string do namespace do site atual
- siteDomains - lista os subdomínios registrados
- isExtraDomain - testa se a requisição atual está em um domínio extra ou no principal

## Services
- AddonDependencyService - auditoria e resolução de dependências entre temas, plugins e projeto
- AddonInstallerService - download e descompactação de addons a partir de URLs
- AddonMarketplaceService - consulta o catálogo e releases do repositório remoto
- AssetManager - orquestrador do enfileiramento de scripts e styles
- ContentExportService - exportação de páginas, posts e taxonomias
- ContentImportService - importação de conteúdos estruturados
- CoreUpdateService - checagem de versão e aplicação de atualizações do núcleo
- EditorManager - ponte de injeção de extensões e scripts entre plugins/temas e o editor de blocos
- SeoResolver - prepara os metadados de SEO e OpenGraph para compartilhar pelas rotas

## Support
- AdminMenu - permite a plugins e temas registrar itens e subitens na barra lateral administrativa
- ApiRegister - permite a plugins e temas registrar endpoints/objetos na REST API
- ContentHelper - sanitização de HTML, limpeza de parágrafos e registro de shortcodes
- Dashboard - permite a plugins e temas injetar boxes na tela inicial da admin
- DynamicRoutes - permite a plugins e temas registrar rotas dinâmicas em tempo de execução
- EmbedService - resolução e suporte para o shortcode [embed]
- HookDiscoverer - varre os arquivos do projeto mapeando hooks declarados
- HookManager - gerenciador central de registro e execução de hooks
- PublicationTypes - permite a plugins e temas registrar novos tipos de publicação (CPT)
- RenderManager - suporte e orquestração do componente <x-render>
- Settings - permite a plugins e temas declarar novos grupos e campos de configuração
- TwoFactorConfig - retorna configurações ativas de 2FA
- TwoFactorRateLimiter - controle de tentativas e limites de segurança do 2FA
- TwoFactorService - geração de segredos e validação de tokens TOTP do 2FA

## Traits
- HasMeta - suporte ao campo JSON/tabela meta em modelos Eloquent
- HasTwoFactor - suporte à autenticação de dois fatores para o model User
- Shortcodes - parser e renderizador de shortcodes em strings de texto

## Componentes com Classe PHP (app/View/Components/)

## Componentes Blade Reutilizáveis por Addons (resources/views/components/)
- <x-switch> - interruptor deslizante liga/desliga com suporte a onChange
- <x-select-input> - dropdown com campo alternativo para digitação de novo valor na hora
- <x-icon-selector> - modal com grade completa do catálogo de ícones Lucide
- <x-modal> - janela sobreposta acessível controlada via eventos modal-open/modal-close
- <x-toast> - notificações flutuantes temporárias disparadas via showToast()
- <x-copy-text> - container monoespaçado com botão de cópia rápida para o clipboard
- <x-chart> - renderizador declarativo de gráficos de barras, linhas ou pizza com Chart.js

## Componentes Blade disponíveis para uso
- <x-hook> - renderiza os callbacks registrados via HookManager
- <x-page-picker> - seletor hierárquico de páginas com exclusão do item atual
- <x-post-picker> - seletor de posts com ordenação feedOrder e badges visuais
- <x-qr-code> - gera e renderiza a imagem vetorial SVG do QR Code
- <x-upload-area> - área interativa de upload drag-and-drop
- <x-password-field> - campo de senha com alternância de visibilidade e medidor de força
- <x-breadcrumbs> - trilha de navegação com resolução automática de rotas e taxonomias
- <x-assessibility> - barra flutuante de acessibilidade unificada
- <x-switch-theme> - alternador visual de tema claro/escuro
- <x-text-size> - botão cíclico de controle dinâmico do tamanho do texto
- <x-vlibras> - botão integrado ao widget oficial do VLibras
- <x-cookie.banner>  - modal de gestão de consentimento LGPD
- <x-cookie.scripts> - injeção condicional de scripts com base no consentimento

## Componentes Blade do Core que podem ser usados por plugins e temas
- <x-editor> - decide entre o TipTap ou o TinyMCE legado pelo conteúdo editado e renderiza o editor
- <x-lost-changes-warn> - aviso de segurança no fechamento ou saída acidental de formulários alterados
- <x-moon-loader> - spinner de carregamento da marca Lunar Base
- <x-media.grid-modal> - modal completo da biblioteca de mídia com suporte a openGridModal()
- <x-media.upload-modal> - modal de upload direto de mídia com barra de progresso

## Componentes Blade Exclusivos do Core
- <x-render> - orquestrador que injeta blocos estruturais de components/rendered/*
- <x-seo-meta> - gera tags <meta> e OpenGraph para o cabeçalho <head>
- <x-configurable-plugin-values> - formata bloco de ajuda para config/pluginSettings.php
- <x-plugin-dependencies> - exigências de pacotes do Composer para plugins
- <x-admin-alert> - exibição central das mensagens flash da sessão (success, warning, error, info)
- <x-admin-help> - botão e modal de ajuda contextual acoplado à rota ativa
- <x-system-update-badge> - indicador e modal com trava de tela para atualização do núcleo via GitHub
- <x-meta-editor> - editor repetível de chave/valor para o campo JSON meta
- <x-render> - tipo de hook exclusivo do core, para evitar repetição de código, onde pode haver <x-hook>

## Camada JavaScript (Frontend da Admin & Utilitários)
- Dialog.js - classe autônoma para diálogos modais (alert, confirm, prompt) com suporte a data-confirm
- PersistentDialog.js - persistência em localStorage/sessionStorage para "Não perguntar novamente"
- preserve-search.js - preservação dinâmica de parâmetros de busca na paginação e abas da admin
- editor-common.js - rotinas e pontes comuns compartilhadas entre editores
- tinymce-editor.js - inicializador e regras do editor legado TinyMCE
- tiptap-editor.js - bundle compilado do editor de blocos Vue 3 + TipTap

## Motor do Editor (TipTap + Vue 3)
- API Global window.LunarEditor:
  - registerBlock() - registra novos blocos no Mega-Menu da Toolbar
  - registerToolbarButton() - adiciona botões diretos na barra superior do editor
  - getExtensions() - injeção de extensões ProseMirror personalizadas
- Blocos Estruturais Nativos do Core:
  - Spacer (Espaçador vertical com alturas rápidas de 20px a 120px)
  - MediaText (Mídia e texto lado a lado com divisões 30/70, 40/60, 50/50 e inversão)
  - CoverBanner (Banner com imagem de fundo, película escura e texto sobreposto)
  - Pullquote (Citação em destaque editorial com autor e temas visuais)
  - ColumnsContainer e ColumnBlock (Grid de 2, 3 ou 4 colunas)
  - ContentCard (Container com temas de borda, cinza ou escuro)
  - Callout (Caixas de aviso com variações de informação, alerta e sucesso)
  - CtaButton (Botão de chamada para ação com alinhamento e cores)
  - Table (Tabelas modernas com manipulador de linhas/colunas e seletor quadriculado)

## Arquivos do Lunar Base em /config
- addons - taxonomia unificada de tags e requisitos de temas e plugins
- admin - configurações da área administrativa (menu, dashboard, skin)
- defaultUsers - perfis e credenciais iniciais para seeds de instalação
- imageSizes - definições de dimensões para geração de variantes de imagem
- pageTemplates - mapeamento de templates Blade disponíveis para páginas
- postTemplates - mapeamento de templates Blade disponíveis para posts
- pluginSettings - valores configuráveis expostos por plugins sem edição de arquivos
- rolesPermissions - matriz de papéis, permissões e capacidades
- scripts - categorias e inventário de scripts externos para consentimento de cookies (LGPD)
- settings - catálogo mestre de definições de formulários da API Settings
- site - dados públicos fundamentais (menu padrão, textos de login, branding)
- tutorials - regras e padrões para o atualizador automatizado de tutoriais
