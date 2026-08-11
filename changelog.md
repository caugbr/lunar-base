# Changelog

## Unreleased

### Added

### Changed

### Fixed

## [1.10.0] 2026-08-10

### Added
- Plugins atualizados com valores configuráveis
- Componente ConfigurablePluginValues
- Adicionado arquivo config/pluginSettings.php para valores configuráveis de plugins
- Plugin Menus agora trabalha em conjunto com os extra domains
- Helper buildMenu agora trabalha com o parâmetro 'domains' (array)

### Changed
- Ajuste no CSS do campo de senha em configurações

## [1.9.0] 2026-08-09

### Added
- Componente Assessibility

### Changed
- Plugin Menus agora mostra o path das páginas como title
- Links para instalar na index de plugins e temas

### Fixed
- Funcionalidade de remover senha em configurações foi restaurada
- Algumas mensagens em inglês agora estão em potuguês

## [1.8.1] 2026-08-07

### Fixed
- Ajustes no sistema de domínios extra

## [1.8.0] 2026-08-07

### Added
- Campo repeater nas settings
- Site domains - gerenciar mais de um domínio no mesmo sistema
- Metadata nas páginas

### Fixed
- Componente meta-editor corrigido

## [1.6.0] 2026-08-06

### Added
- Opção (setting) para exigir verificação de email no cadastro

### Changed
- Emails agora tem um layout separado (emails.layout)

### Fixed
- Criação de links para temas e plugins agora usam o método interno do Laravel

## [1.5.0] 2026-08-05

### Changed
- PublicationTypes.php modificado para preparar tipos extra para exportação
- Plugin Backup agora adiciona seu link no menu sob Ferramentas
- Campos de senha nas configurações agora tem um toggle pra ver o texto digitado

### Added
- Plugins e temas agora tem link para atualizar quando a versão no github for superior
- View admin.tools.tool-card para adicionar um tool card em Ferramentas
- Área de ferramentas do sistema
- Exportar e importar páginas, posts, taxonomias e conteúdos registratos por plugins
- Adicionado o plugin Asaas para pagamentos com o banco Asaas

### Fixed
- Criação dos links para temas e plugins ajustada para usar Str::slug

## [1.4.1] 2026-08-01

### Changed
- Migrations limpas, geradas novamente a partir do banco

### Added
- Posts em destaque na home via <x-render name="featured_posts" />
- Componente Render para adicionar trechos de código nas views - exclusivo do core
- Taxonomias agora podem ser definidas para um tipo específico de publicação
- Plugin e temas podem registrar novos tipos de publicação e ter taxonomias exclusivas

### Fixed
- System update agora usa Dialog e o CSS carrega
- Sticky posts agora aparecem no início da listagem no blog
- Corrigida a validação do term_ids ao salvar posts e páginas

## [1.3.0] 2026-07-30

### Changed
- Menu admin agora abre submenu no clique da setinha
- AdinMenu.php agora tem métodos pra adicionar novos grupos ao menu
- README.md simplificado
- Comandos para criar plugins e temas modificados - substituímos kebab por slug

### Added
- Novas variáveis no .env: GIT_SYSTEM_REPO e GIT_ADDONS_REPO (update e marketplace)
- Plugin Backup
- Checagem de versão e atualização com um clique
- Adicionado o componente chart, usando o chat.js via CDN
- REST API genérica adicionada (readonly, expõe apenas o que já é público)

### Fixed
- Componente upload-area corrigido (attr accept não funcionava)
- Agora excerpts dos posts não mostram mais shortcodes

## [1.2.0] 2026-07-26

### Changed
- Comandos para criar plugins e temas não criam mais os links simbólicos
- Ativação e desativação de plugins e temas agora gerenciam os links simbólicos

### Added
- Plugins e temas agora ficam fora do projeto e são baixados direto do github
- Comandos plugin:link e theme:link agora também aceitam a flag --unlink
- DbHelper.php adicionado com a função global dbAvailable()
- O comando de criação de temas agora leva arquivos fora da pasta public
- Comando theme:create agora clona todas as views públicas para o tema
- Comando theme:create agora clona todos os arquivos css para o tema
- Plugin Prism Highlight adicionado

### Fixed
- Providers que davam problema na instalação consertados
- Arquivo routes/web.php que dava problema na instalação consertado
- Remover usuário consertado no controller

## [1.1.0] 2026-07-20

### Changed
- Paginação normalizada com custom view para toda a admin
- Menu admin reorganizado
- Popups corrigidos para abrir / fechar suavemente com transition
- Chamadas JS a alert() e confirm() agora usam Dialog
- Forms e links agora podem usar Dialog através do attr data-confirm
- Box Metadados em posts agora precisa de uma setting para aparecer
- Menu admin agora aceita subitens
- Plugin e temas podem adicionar subitens em itens existentes
- Plugin Comments agora tem uma interface de moderação na admin

### Added
- Plugin Shortcode do TinyMCE atualizado para mostrar nomes e atributos dos shortcodes
- Shortcodes agora são registrados com descrição, exemplo e atributos
- Shortcode [embed], usando embed/embed
- JS global dialog.js (alert, confirm, prompt)
- Filtro na visualização de hooks na admin
- Plugin Populator
- Plugin Galleries
- Comando para atualizar os tutoriais com nome e versão a cada atualização
- Imagens vinculadas agora mostram a publicação relacionada no popup de edição
- Thumbnails agora mostram a publicação relacionada no popup de edição
- Plugin Populate adicionado
- Descrição para todos os hooks do sistema adicionada à admin
- Interface de hooks adicionado à admin
- Plugin Banners adicionado
- Agora getSettingsDefinitions, settingsAll e settingsGroup tem a opção de pegar apenas settings do sistema, evitando as injetadas por plugins e temas

### Fix
- Arquivo de rotas que depende de setting() modificado pra não dar erro na instalação
- Providers que usam database atrapalhavam o composer install - corrigidos
- Componente select-input consertado
- Correção na ER em HookDiscoverer

## [1.0.0] 2026-07-12
- Definido um início arbitrário
