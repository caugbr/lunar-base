# Changelog

## Unreleased

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
- Imagens vinculadas agora mostram a publicação relacionada no popup de edição
- Thumbnails agora mostram a publicação relacionada no popup de edição
- Plugin Populate adicionado
- Descrição para todos os hooks do sistema adicionada à admin
- Interface de hooks adicionado à admin
- Plugin Banners adicionado
- Agora getSettingsDefinitions, settingsAll e settingsGroup tem a opção de pegar apenas settings do sistema, evitando as injetadas por plugins e temas

### Fix
- Componente select-input consertado
- Correção na ER em HookDiscoverer

## [1.0.0] 2026-07-12
- Definido um início arbitrário
