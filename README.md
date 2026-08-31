
# Lunar Base

O **Lunar Base** é um Starter Kit híbrido para Laravel 12, projetado com características de CMS modular para servir como base sólida e flexível no desenvolvimento de aplicações web.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## Addons
O Lunar Base trabalha com o conceito de temas e plugins. Esse material está em um repositório separado,  que serve de base para baixar e atualizar através da própria interface. Veja no repositório: https://github.com/caugbr/lunar-base-addons.

## Instalação

O projeto conta com o script `install.sh` para automatizar e guiar a instalação de forma interativa.

### Requisitos

- PHP >= 8.3
- Composer
- Ambiente Bash (Git Bash ou WSL se estiver no Windows)

### Execução

```bash
chmod +x install.sh
./install.sh
```

### Opções do Script

| Flag | Descrição |
| :--- | :--- |
| `./install.sh --help` | Exibe a ajuda e o roteiro de execução |
| `./install.sh --dry-run` | Executa apenas a coleta de dados e gera o JSON, sem instalar nada |

### Coleta de Dados Interativa

1. **Informações do site**: Nome e URL (padrões: `Lunar Base`, `http://localhost`)
2. **Administrador principal**: Nome, e-mail e senha
3. **Usuários de demonstração**: Senha padrão para perfis de testes (`role@dominio`)
4. **Persistência de dados**: Opção de salvar credenciais em `storage/app/.install/default_users_data.json` para futuros seeds
5. **Banco de dados**: Opção entre `sqlite` (padrão), `mysql`, `pgsql` ou `sqlsrv`

### Etapas da Instalação Automática

1. Geração do arquivo temporário com os usuários
2. Instalação das dependências via `composer install`
3. Criação do arquivo `.env` e geração da `APP_KEY`
4. Atualização das variáveis no `.env` (`APP_NAME`, `APP_URL`, `DB_*`)
5. Criação do arquivo de banco SQLite (se selecionado)
6. Execução de migrações (`php artisan migrate --force`)
7. Povoamento do banco (`php artisan db:seed --force`)
8. Criação do link simbólico (`php artisan storage:link`)
9. Limpeza e otimização dos caches do sistema
10. Exibição do resumo final de credenciais criadas

> **Fallback de Usuários:** Se o JSON de instalação não existir, a aplicação utilizará a configuração em `config/defaultUsers.php` gerando os acessos a partir das roles em `config/rolesPermissions.php` com a senha padrão `Pass#1029`.

---

## Estrutura do Painel Administrativo

| Módulo | Descrição |
| :--- | :--- |
| **Dashboard** | Visão geral e métricas do sistema. |
| **Páginas** | Gerenciador de páginas estáticas e dinâmicas (com atalho para *Nova Página*). |
| **Posts** | Gerenciador de publicações e artigos do blog (com atalho para *Novo Post*). |
| **Mídia** | Biblioteca centralizada para upload e gestão de arquivos e imagens. |
| **Taxonomias** | Categorização de conteúdos (com gestão de *Taxonomias* e *Termos*). |
| **Usuários** | Gerenciamento de acessos e contas do sistema (com atalho para *Novo Usuário*). |
| **Plugins** | Gerenciador de extensões ativas e acesso ao *Marketplace de Plugins*. |
| **Temas** | Gerenciador de temas visuais e acesso ao *Marketplace de Temas*. |
| **Configurações** | Painel para parametrização de variáveis globais do sistema. |
| **Ferramentas** | Exportar ou exportar posta, pages, taxonomias e outros. |
| **Referências** | Documentação e auditoria técnica interna (*Hooks*, *Shortcodes*, *Permissões* e *Logs*). |

---

## Licença

Este projeto é licenciado sob a [MIT License](https://mit-license.org/).
