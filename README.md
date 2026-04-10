# 🌙 Lunar Widgets

**Widgets astrológicos interativos** para sites, blogs e aplicações.

## 📌 Sobre o projeto

Lunar Widgets é uma plataforma que oferece **widgets personalizados** baseados na posição da Lua no nascimento. O primeiro widget é focado na relação **mãe e bebê**, oferecendo interpretações emocionais e dicas de convivência nos primeiros anos de vida.

A ideia nasceu da necessidade de oferecer um **conteúdo significativo e personalizado** para mães que buscam compreender melhor seus bebês — e também para qualquer pessoa que queira explorar conexões emocionais guiadas pela astrologia.

O sistema é **extensível**: novos widgets podem ser criados para diferentes relações (dono e pet, casais, etc.) mantendo a mesma estrutura de cálculo e administração.

---

## 🧠 Como funciona

O coração do sistema é o **cálculo da posição da Lua** usando efemérides astronômicas precisas (Swiss Ephemeris). A partir da data e hora de nascimento de duas pessoas (ou de um tutor e seu pet), o sistema:

- Calcula a **longitude da Lua** de cada um
- Determina o **signo lunar** e a **fase da Lua** no nascimento
- Identifica **aspectos** entre as luas (conjunção, oposição, etc.)
- Busca textos interpretativos em um banco de dados com mais de 180 entradas
- Retorna uma análise completa, incluindo dicas de convivência e reflexões sobre o vínculo emocional

Os widgets podem ser instalados em qualquer site com apenas algumas linhas de código, e os parceiros que integram o widget têm acesso a um **painel administrativo** com estatísticas de vendas e comissões.

---

## 🧩 Tecnologias

### Backend
- **Laravel 11** (PHP)
- **MySQL** (banco de dados)
- **Swiss Ephemeris** (cálculos astronômicos)
- **Sanctum** (autenticação via token para API)
- **Autenticação web** com login e roles (admin / parceiro)

### Frontend (widget)
- **Vue 3** (com `<script setup>`)
- **Composition API**
- **Vite** (build e desenvolvimento)
- **CSS puro** (sem Tailwind)

### Administração
- Painel **admin** completo (CRUD de parceiros, estatísticas)
- Painel do **parceiro** (dashboard, histórico de vendas)
- **Autenticação em dois fatores** prevista (futuro)

---

## 🚀 Como usar

### Para desenvolvedores

1. Clone o repositório
2. Configure o arquivo `.env` com as credenciais do banco de dados
3. Execute as migrações e seeders:
   `php artisan migrate:fresh --seed`
4. Inicie o servidor local:
   `php artisan serve`
5. Acesse `http://localhost:8000`

### Para parceiros

Inclua o widget no seu site com:

`<script src="https://seusite.com/widget/lunar-widget-loader.js" data-lunar-widget data-partner="seu_token_aqui"></script>`

O widget será carregado no local da página onde o script for inserido.

---

## 📁 Estrutura simplificada

    lunar-api/                → Backend Laravel
    app/
        Models/               → Partner, Widget, Calculation, etc.
        Services/             → MoonCalculator (cálculos)
        Http/Controllers/     → Admin, Partner, API
    database/
        migrations/
        seeders/              → Dados iniciais (textos, parceiros, cidades)
    storage/sweph/            → Efemérides e binário do swetest
    resources/views/          → Painéis admin e parceiro

    lunar-widget/             → Frontend Vue
    src/                      → Código fonte do widget
    dist/                     → Arquivos compilados

---

## 🧠 Próximos passos

- [x] Área administrativa
- [x] Dashboard do parceiro
- [x] CRUD de parceiros
- [x] Filtros e paginação nas listas de vendas
- [ ] Interface para edição de textos astrológicos
- [ ] Sistema de pagamento (Asaas)
- [ ] Autenticação em duas etapas (2FA) para admin

---

## 🌙 Licença

Projeto em desenvolvimento. Todos os direitos reservados.

---

## 📫 Contato

Para parcerias, dúvidas ou sugestões: contato@lunarwidgets.com
