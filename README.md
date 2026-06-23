# Lunar Base

O **Lunar Base** é um Starter Kit híbrido para Laravel, projetado com características de CMS modular. Ele gerencia seu comportamento operacional através de estruturas declarativas em arquivos de configuração locais, traits de expansão e helpers de contexto, oferecendo uma base sólida e flexível para o desenvolvimento de aplicações web robustas.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white" alt="PHP"><br>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel"><br>
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License"><br>
  <img src="https://img.shields.io/badge/CAPTCHA-Turnstile-FFA500?logo=cloudflare&logoColor=white" alt="Captcha"><br>
  <img src="https://img.shields.io/badge/2FA-TOTP-blue" alt="2FA">
</p>

## Características

- **Painel Administrativo Intuitivo**: Interface limpa e organizada com módulos para gerenciamento completo do sistema.
- **Arquitetura Declarativa**: Comportamento do sistema definido por arquivos de configuração (`.php`), eliminando queries repetitivas e melhorando o TTFB (tempo de resposta do servidor).
- **RBAC Estático**: Controle de acesso baseado em papéis (Role-Based Access Control) definido em código, garantindo segurança e clareza.
- **Roteamento Híbrido**: Sistema inteligente de roteamento que resolve dinamicamente URLs amigáveis para páginas, posts e taxonomias.
- **Shortcodes**: Motor de renderização de conteúdo dinâmico via tags personalizadas `[shortcode]`.
- **Helpers Globais**: Funções utilitárias para acesso a configurações, permissões e manipulação de dados.
- 
## Funcionalidades

*   **Gestão de Conteúdo Completa**: CRUDs robustos para Páginas estáticas e Posts cronológicos (Blog).
*   **Mídia Polimórfica**: Biblioteca de mídia centralizada com redimensionamento automático e suporte a imagens (incluindo WebP/SVG) e documentos.
*   **Formulários Dinâmicos**: Construtor de formulários via admin com renderização automática via Shortcodes.
*   **Segurança Avançada**:
    *   Autenticação de Dois Fatores (2FA) nativa via TOTP (Google Authenticator/Authy).
    *   Integração opcional com Cloudflare Turnstile (CAPTCHA).
    *   Sistema de RBAC (Role-Based Access Control) estático e imutável via código.
*   **SEO Nativo**: Gerador automático de metadados Open Graph e Twitter Cards integrado aos modelos.
*   **Logs de Auditoria**: Registro automático de ações administrativas para segurança e rastreabilidade.
*   **Arquitetura Baseada em Configuração**: Altere o comportamento do core sem modificar o código-fonte, apenas editando arquivos em `/config`.

---

## Stack Técnica

*   **Backend**: Laravel 12+ (PHP 8.2+)
*   **Frontend**: Blade + Alpine.js
*   **Editor**: TinyMCE customizado para editar shortcodes.
*   **Segurança**: Turnstile / Google2FA + BaconQrCode.
*   **Ícones**: Lucide Icons (via Blade Components).

---

## Módulos do Painel Administrativo

| Módulo         | Descrição                                                                                                                               |
| :------------- | :-------------------------------------------------------------------------------------------------------------------------------------- |
| **Dashboard**  | Ponto de ancoragem para futuros widgets, gráficos e relatórios gerenciais.                                                              |
| **Páginas**    | CRUD para páginas estáticas/dinâmicas com suporte a thumbnails, seleção de layouts (`/config/pageTemplates.php`) e namespaces de URL. |
| **Posts**      | Gerenciador de publicações cronológicas (blog/notícias) com agendamento, destaque (`featured`) e fixação (`sticky`).                    |
| **Mídia**      | Biblioteca central de uploads com redimensionamento automático e compressão de imagens.                                                 |
| **Taxonomias** | Agrupamentos relacionais para categorizar e filtrar Páginas e Posts.                                                                    |
| **Formulários** | Estrutura de captura de dados renderizada via shortcode `[form slug="..."]`, com validação, notificações SMTP e feedback ao usuário.  |
| **Usuários**   | Gerenciamento de identidades e credenciais administrativas.                                                                             |
| **Permissões** | Interface *Read-Only* para auditoria do sistema RBAC definido em `/config/rolesPermissions.php`.                                      |
| **Configurações** | Painel dinâmico para parametrização de variáveis globais (SEO, mídia, SMTP, etc.) baseado no schema `/config/settings.php`.         |
| **Logs**       | Trilha de auditoria (Audit Trail) para monitoramento de ações críticas do sistema.                                                      |

## Estrutura de Arquivos de Configuração

A lógica central do Lunar Base é guiada por arquivos de configuração declarativos:

- **`/config/adminMenu.php`**: Define a estrutura do menu lateral administrativo (rótulos, ícones, rotas e permissões).
- **`/config/rolesPermissions.php`**: Repositório estático para definir papéis (roles) e suas respectivas permissões.
- **`/config/settings.php`**: Schema do painel de configurações gerais, definindo campos, tipos e valores padrão para cada grupo.
- **`/config/pageTemplates.php`** & **`/config/postTemplates.php`**: Listam os arquivos Blade utilizáveis como templates para páginas e posts.
- **`/config/site.php`**: Concentra diretivas estruturais do site, como o menu principal de navegação (`config('site.mainMenu')`).

## Helpers e Traits Globais

### `SettingHelper`

Facilita o acesso às configurações do sistema, mesclando valores do banco de dados com os *fallbacks* definidos em `/config/settings.php`.

```php
// Obtém o valor de uma configuração específica
$siteName = setting('general.site_name');

// Retorna todas as configurações de um grupo
$smtpConfig = settingsGroup('mail');

// Retorna todas as configurações do sistema
$allSettings = settingsAll();

// Obtém o valor padrão estático de uma configuração
$fallbackTheme = settingDefault('general.site_theme');
```

### `Shortcodes` (Trait)

Processa blocos textuais e renderiza dinamicamente conteúdo no front-end através do método `ContentHelper::parseShortcodes($content)`. Para adicionar um novo shortcode, crie um método privado na trait seguindo o padrão `render{NomeDoShortcode}`.

```php
// Estrutura para a tag [form slug="meu-formulario"]
private static function renderForm($attributes) {
    $slug = $attributes['slug'] ?? null;
    // ... lógica para buscar e renderizar o formulário
}
```

### `RolePermissionHelper`

Fornece validadores booleanos para verificação rápida de papéis e permissões em controladores e views Blade.

```php
// Verifica se o usuário logado tem um papel específico
if (isRole('admin')) {
    // Código para administradores
}

// Verifica se o usuário tem uma permissão específica
if (userCan('manage-pages')) {
    // Código para quem pode gerenciar páginas
}
```

## Orquestrador de Permalinks e Roteamento Híbrido

O sistema utiliza um roteamento híbrido para evitar conflitos e oferecer flexibilidade. As requisições públicas são capturadas no final do arquivo `routes/web.php` e centralizadas no `RouteOrchestratorController`. Este componente analisa a URL com base nos segmentos e as despacha para o controller correto, disparando um erro 404 caso nenhuma definição seja encontrada.

```php
// Captura de rota para URLs com 3 segmentos (ex: base/namespace/slug)
Route::get('/{base}/{namespace}/{slug}', [RouteOrchestratorController::class, 'handleThreeSegments']);

// Captura de rota para URLs com 2 segmentos (ex: base/slug)
Route::get('/{base}/{slug}', [RouteOrchestratorController::class, 'handleTwoSegments']);

// Captura de rota para URLs com 1 segmento (ex: base)
Route::get('/{base}', [RouteOrchestratorController::class, 'handleOneSegment']);
```

## Instalação

Se você tem intimidade com o Laravel e preferir fazer manualmente, siga seu roteiro de instalação, mas temos o script `install.sh` automatiza toda a instalação do Lunar Base de forma interativa.

### Requisitos

- PHP >= 8.1
- Composer
- Bash (Git Bash se estiver no Windows)

### Uso básico

```bash
chmod +x install.sh
./install.sh
```

### Opções

| Flag | Descrição |
|------|-----------|
| `./install.sh --help` | Exibe o roteiro de instalação |
| `./install.sh --dry-run` | Executa apenas as perguntas e gera o JSON, sem instalar |

### O que será perguntado

1. **Informações do site** — nome e URL (padrões: `Lunar Base`, `http://localhost`)
2. **Administrador principal** — nome, e-mail e senha (com confirmação)
3. **Usuários de demonstração** — senha única para todos os roles. E-mails gerados automaticamente como `role@dominio.com`
4. **Persistência** — se deseja manter os dados em `storage/app/.install/default_users_data.json` para futuros seeds
5. **Banco de dados** — `sqlite` (padrão), `mysql`, `pgsql` ou `sqlsrv`. Se não for SQLite, pergunta host, porta, nome, usuário e senha

### O que o script faz

1. Gera `storage/app/.install/default_users_data.json` (será utilizado durante o seed)
2. `composer install`
3. Cria `.env` a partir de `.env.example` (se não existir)
4. Gera `APP_KEY`
5. Preenche `APP_NAME`, `APP_URL` e `DB_*` no `.env`
6. Cria `database/database.sqlite` (se SQLite)
7. `php artisan migrate --force`
8. `php artisan db:seed --force`
9. `php artisan storage:link`
10. Limpa caches
11. Remove ou preserva o JSON temporário

### Fallback sem JSON

Se `default_users_data.json` não existir, `config/defaultUsers.php` gera usuários automaticamente a partir de `config/rolesPermissions.php`:
- E-mails: `role@lunar.base` (ou domínio do `APP_URL`)
- Senha padrão: `Pass#1029`

### Windows

Requer ambiente Bash: **Git Bash** (recomendado), **WSL** ou **Cygwin**.

### Arquivos de configuração

| Arquivo | Função |
|---------|--------|
| `config/rolesPermissions.php` | Roles e permissions |
| `config/defaultUsers.php` | Array de usuários (lê JSON ou fallback) |
| `database/seeders/AdminUsersSeeder.php` | Cria usuários via `config('defaultUsers')` |
| `storage/app/.install/default_users_data.json` | Dados temporários do install.sh |

## Licença

Este projeto é licenciado sob a [MIT License](https://mit-license.org/).

