# Changelog

## Unreleased

### Changed
- README.md simplificado
- Comandos para criar plugins e temas modificados - kebab por slug

### Added
- REST API genérica adicionada

### Fixed
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
