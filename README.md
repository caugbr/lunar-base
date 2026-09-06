
# Lunar Base

O **Lunar Base** é um Starter Kit híbrido para Laravel 12, projetado com características de CMS modular para servir como base sólida e flexível no desenvolvimento de aplicações web.

*This is a work in progress*

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## Addons
O Lunar Base trabalha com o conceito de temas e plugins. Esse material está em um repositório separado,  que serve de base para baixar e atualizar através da própria interface. Veja no repositório: https://github.com/caugbr/lunar-base-addons.

## Principais Funcionalidades

### Gestão de Conteúdo & Edição
* **Páginas e Posts:** Gerenciamento completo de publicações, rascunhos, agendamentos, autores e categorias.
* **Editor de Blocos Moderno (Tiptap / Vue 3):** Edição estruturada em árvore JSON com salvamento duplo (JSON + HTML pré-renderizado de alta performance).
* **Barra de Ferramentas Configurável:** Controle visual no painel administrativo para habilitar/desabilitar botões do editor e definir a paleta de cores da marca.
* **Blocos Nativos Inclusos:** Caixas de aviso (Callouts), grid de colunas responsivo (2 a 4), cards de conteúdo, botões CTA, tabelas interativas, imagens com legenda/redimensionamento e bloco de código com realce de sintaxe.
* **Sistema de Shortcodes Visual:** Badges coloridos no editor com formulários gerados dinamicamente com base no esquema de atributos do PHP.
* **SEO Automático Inteligente:** Geração nativa de OpenGraph e meta tags com base em títulos, resumos e thumbnails.
* **Importação e Exportação:** Backup, migração e transporte de conteúdos com facilidade.

---

### Ecossistema de Temas & Plugins
* **Marketplace Integrado:** Instalação, ativação e atualização de plugins e temas com 1 clique direto no painel administrativo.
* **Mais de 20 Plugins Prontos:** Ecossistema inicial com mais de duas dezenas de extensões funcionais prontas para uso.
* **Extensibilidade Total:** Plugins e temas podem:
  * Injetar novos blocos, ferramentas e botões na toolbar do editor em tempo de execução.
  * Criar novos grupos e campos no painel de configurações.
  * Injetar e substituir elementos em views Blade via **Sistema de Hooks** (`<x-hook />`).
  * Adicionar itens e páginas no menu administrativo.
  * Registrar **Rotas Dinâmicas** públicas em tempo de execução.
  * Criar novos widgets para o Dashboard.
  * Registrar shortcodes customizados com validação e interface.

---

### Engenharia, Persistência & Assets
* **Atualização do Core em 1 Clique:** Atualização do núcleo do sistema diretamente pelo painel administrativo.
* **Asset Pipeline Inteligente (AssetManager):** Enfileiramento de scripts e estilos (`add_style`, `add_script`) com controle de dependências, handles únicos, deduplicação e injeção inline.
* **Settings & Options API:**
  * *Settings:* Criação declarativa de formulários administrativos (switches, selects, number, color picker).
  * *Options:* Armazenamento leve de chave/valor tipado (com casting automático para arrays e encriptação nativa).
* **REST API Nativa:** Endpoints prontos para consumo de dados por aplicações externas.
* **Ajuda Contextual Integrada:** Tutoriais e documentação de desenvolvedor acessíveis diretamente na interface administrativa.
* **Logs de Ações (Audit Trail):** Rastreamento e auditoria de ações executadas pelos usuários no painel.

---

### Segurança & Controle de Acesso
* **Controle de Acesso (Roles & Permissions):** Gestão de papéis e permissões para administradores, editores, autores e assinantes.
* **2FA Nativo (TOTP):** Autenticação de dois fatores integrada com suporte ao Google Authenticator e Authy.
* **Proteção CAPTCHA:** Suporte nativo ao Cloudflare Turnstile para proteção contra bots em formulários e login.
* **Proteção de Formulários:** Alerta automático de alterações pendentes antes de fechar ou recarregar abas (`<x-lost-changes-warn />`).

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
| **Ferramentas** | Exportar ou exportar posta, páginas, taxonomias e outros. |
| **Referências** | Documentação e auditoria técnica interna (*Hooks*, *Shortcodes*, *Permissões*, *Logs* e *Tutoriais*). |

---

Projeto desenvolvido por [Cau Guanabara](https://cauguanabara.com.br) e licenciado sob a [MIT License](https://mit-license.org/).
