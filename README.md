# Lunar Base

O Lunar Base é um Starter Kit híbrido para Laravel 12, projetado como um CMS modular, extensível e pronto para produção. Une a flexibilidade de desenvolvimento do ecossistema Laravel à convenência de um painel administrativo com temas, plugins e edição visual.

*This is a work in progress*

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>


## Ecossistema de Temas e Plugins (Addons)

O Lunar Base gerencia complementos de forma desacoplada atravës do repositório oficial caugbr/lunar-base-addons, que atua como marketplace nativo:

* Instalação e Atualização em 1 Clique: Baixe, atualize e gerencie complementos direto pelo painel.
* Gestor de Dependências Automático: Detecta se um tema ou plugin requer outras extensões e oferece atalhos para download e ativação imediata.
* Live Preview de Temas: Teste temas visuais em tempo real por iframe antes de ativá-los publicamente.
* Asset Pipeline Isolado: Links simbólicos automáticos (public/plugins/ e public/themes/) criados e removidos dinamicamente na ativação/desativação.
---

## Principais Funcionalidades

### Gestão de Conteúdo e Colaboração

* Paginas, Posts e CPTs: Gestão de conteúdo estruturado, rascunhos, agendamento de publicações e taxonomias hierárquicas ou símples.
* Editor em Blocos (Tiptap / Vue 3): Edição moderna baseada em Árvore JSON + HTML com barra de ferramentas e paleta de cores configuráveis.
* Bloqueio de Concorrência (Content Lock): Proteção em tempo real via Heartbeat API que impede conflito entre múltiplos usuários editando o mesmo material (com suporte a Take Over).
* Motor de Imagens Avançado: Upload com compressão automática (WebP/JPEG), recorte inteligente centrado em Ponto Focal (Focal Point) e geração dinâmica de variantes.
* Shortcodes e Embeds: Suporte nativo a tags [embed], [script], [style], [link] e shortcodes customizados registrados por plugins.
* Importação e Exportação: Migração completa de conteúdos, taxonomias e vínculos via arquivos JSON.

### Roteamento e Multidomínio

* Orquestrador de Rotas Dinâmicas: URLs limpas com bases personalizáveis (/blog, /pagina, /taxonomia) e suporte a rotas injetadas dinamicamente em tempo de execução.
* Suporte a Múltiplos Domínios e Namespaces: Segmentação de páginas e menus por domënio ou subdomínio configurado nas settings.
* Headless Ready (Resolve API): Endpoint universal (/api/v1/resolve?path=...) capaz de resolver páginas, posts e rotas de plugins para consumo em SPAs (Nuxt/Vue).

### Extensibilidade para Desenvolvedores

* Barramento Completo de Hooks:
	* PHP Actions & Filters: addAction(), doAction(), addFilter(), applyFilters().
	* Template Hooks: Injeção e substituição de blocos no Blade com &lt;x-hook name="..." /&gt;.
	* Hooks Scanner CLI: Varredura estática de código com compilação de cache em bootstrap/cache/hooks.php.
* Publication Types API: Registre novos tipos de conteúdo (cursos, produtos, portfólios) com injeção automática de eventos Eloquent e campos de busca.
* Settings & Options API: Interface declarativa para criar campos (switches, cores, repeaters, selects) persistidos com suporte a tipagem e criptografia nativa.
* AssetManager: Enfileiramento ordenado de scripts, folhas de estilo e códigos inline (addStyle, addScript).

### Segurança & Auditoria

* Controle de Acesso (RBAC): Papéis e permissóes granulares dinâmicos integrados com os Gates do Laravel (@permission, @isRole).
* 2FA Híbrido: Autenticação em duas etapas via aplicativo autenticador (TOTP) ou código temporário por E-mail (OTP), com limitador de tentativas (Rate Limiter).
* Trilha de Auditoria (Admin Logs): Registro automático de ações senísveis (criação, edição, exclusão, acessos com IP e User-Agent).
* Proteção contra Bots: Suporte integrado ao Cloudflare Turnstile na tela de autenticação.
* Avisos Administrativos Persistentes: Notificações segmentadas por papel (role) ou usuário, com histórico de dispensa.

## Ferramentas CLI (Artisan)

O Lunar Base traiz geradores de código específicos para acelerar o desenvolvimento de extensões:

```bash
# Criação estruturada de complementos
php  artisan  plugin:create  {nome}
php  artisan  theme:create  {nome}

# Gestão de links e metadados
php  artisan  plugin:link  {nome}
php  artisan  theme:link  {nome}
php  artisan  plugin:edit-tags
php  artisan  theme:edit-tags

# Varredura de Hooks e Utilitários
php  artisan  hooks:scan  # Compila todos os hooks Blade e PHP para cache
php  artisan  config:lang  {locale}  # Extrai termos configuráveis para JSON em /lang
php  artisan  user:quick-create  # Criação rápida de usuários pelo terminal
php  artisan  migrate:fake  # Marca migrations como executadas sem rodá-las
```
---
## Módulos do Painel Administrativo

| Módulo | Descrição |
| :--- | :--- |
| **Dashboard** | Métricas gerais do sistema e suporte a widgets adicionados via API. |
| **Páginas** | Gerenciador de páginas estáticas, dinâmicas e com namespace. |
| **Posts** | Artigos do blog, agendamentos, autores e destaques fixados. |
| **Mídia** | Biblioteca de uploads, ponto focal, gerenciamento de variantes e redimensionamento. |
| **Taxonomias** | Categorias, tags e taxonomias personalizadas (hierárquicas ou únicas). |
| **Usuários** | Contas, níveis de acesso (roles/permissions) e gestão de 2FA. |
| **Avisos** | Sistema de comunicados persistentes direcionados no topo da administração. |
| **Plugins** | Gerenciamento local e cathálogo de instalação direta via Marketplace. |
| **Temas** | Galeria de temas com preview interativo e ativação com 1 clique. |
| **Configurações** | Central declarativa de opções visuais, e-mail SMTP, multisite e leitura. |
| **Ferramentas** | Importação e exportação de dados em lote no formato JSON. |
| **Referências** | Guia interno de auditoria: Hooks PHP/Blade, Shortcodes, Permissões e Logs. |
| **Atualização do Core** | Verificação de releases e atualização completa do núcleo com 1 clique. |

---
## Licença

Desenvolvido por Cau Guanabara (https://cauguanabar.com.br).
Distribuído sob a licença MIT (https://mit-license.org/).
