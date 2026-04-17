# 🌙 Lunar Base

Starter Kit Laravel para desenvolvimento ágil de painéis administrativos e CMS.  
Focado em estrutura sólida, componentes reutilizáveis e fluxo de trabalho otimizado.

---

## Funcionalidades

- **Gestão de Usuários & Permissões**: Controle granular de acesso com papéis (Roles) e políticas.
- **Páginas Dinâmicas**: Editor rich text (TinyMCE), thumbnails, galerias e taxonomias hierárquicas.
- **Biblioteca de Mídia Inteligente**: Upload com metadados, relações polimórficas, filtros por vínculo e integração nativa com o editor.
- **Taxonomias Flexíveis**: Categorias, tags e termos aninhados para classificação de conteúdo.
- **Painel de Configurações**: Gerenciamento centralizado de opções e variáveis do site.
- **Arquitetura Leve**: Blade Components + Alpine.js 3. Zero dependência de SPAs ou build steps complexos.

---

## Stack

| Camada        | Tecnologia                          |
|---------------|-------------------------------------|
| **Backend**   | Laravel 11 / PHP 8.3+               |
| **Frontend**  | Blade, Alpine.js 3, CSS puro        |
| **Editor**    | TinyMCE (integrado com seletor)     |
| **Ícones**    | Lucide Icons (Blade components)     |
| **Assets**    | Servidos direto de `public/` (Sem Vite) |
| **Banco**     | MySQL / PostgreSQL / SQLite         |

---

## Instalação

#### 1. Clonar e entrar no diretório
git clone https://github.com/caugbr/lunar-base.git lunar-base \
cd lunar-base

#### 2. Instalar dependências
composer install

#### 3. Configurar ambiente
cp .env.example .env
#### → Edite APP_URL, DB_* e demais credenciais

#### 4. Gerar chave e preparar banco
php artisan key:generate \
php artisan migrate --seed

#### 5. Criar link de armazenamento (Essencial para mídia)
php artisan storage:link

#### 6. Servir
php artisan serve

## Notas de Uso

### Mídia e Storage
*   **Caminho:** Os arquivos físicos ficam em `storage/app/public`.
*   **Link:** O comando `storage:link` é obrigatório para que o `public/` acesse os arquivos.
*   **Lógica de Vínculo:** As imagens são associadas às páginas via `mediaable_id` e `mediaable_type`. O vínculo só ocorre no momento do `save()` do formulário em create / edit de páginas.

### Layout da admin
*   **Zero Vite:** CSS e JS não compilam. Edite diretamente em `public/`.
*   **Reatividade:** O Alpine.js controla os modais e outras coisas no editor de páginas.
*   **Estrutura:** O layout principal é `resources/views/admin/layout.blade.php`. Use `@extends`, `@section` e `@push` para injetar estilos e scripts específicos.

### Configurações
*   **Admin:** Edite os itens do menu e outros detalhes da admin em `config/admin.php`.
*   **Site:** Adicione ou remova itens de configuração em `config/settings.php`. Use as funções do helper (carregadas globalmente) para recuperar os valores salvos:
```
setting('item_name', 'default value'); // valor individual
settingsGroup('group_name'); // array com todos os valores de um grupo
settingsAll(); // todos os valores salvos
```

### 📄 Templates e Layouts de Páginas

O sistema permite personalizar a exibição das páginas separando o conteúdo da estrutura global:

*  **Templates:** Contêm a lógica de conteúdo da página. Devem ser criados em `resources/views/public/templates/`.
    *   *Nota:* Após criar o arquivo, registre-o em `config/pageTemplates.php` para que ele apareça como opção no painel admin.
*  **Layouts:** São as "molduras" HTML (head, header, footer). Devem ser criados em `resources/views/public/`.

Cada template deve referenciar um layout base utilizando a diretiva `@extends('public.nome-do-layout')`.

### Acesso
*   **Rota Base:** `/admin`
*   **Middleware:** Protegido por auth e verificação de Roles (Viewer não acessa a admin).
*   **Seed:** Usuário padrão criado no migration/seed (verifique `DatabaseSeeder`).

## Licença

Distribuído sob a **Licença MIT**.

> **Atribuição:** Se usar este kit como base para outros projetos, peço apenas que mantenha o crédito ao autor original nos arquivos.

Criado por Cau Guanabara.

