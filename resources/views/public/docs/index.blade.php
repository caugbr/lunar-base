@extends('public.site-layout')

@section('title', 'Documentação Técnica de Arquitetura — Lunar Base')
@section('meta_description', 'Guia completo de referência sobre o ecossistema, módulos, infraestrutura e engenharia do Lunar Base.')

@section('content')
<div class="lunar-logo">
    <img src="{{ asset('images/lunar-base.png') }}" alt="Lunar Base - Laravel Starter Kit">
</div>

<div class="lunar-doc-container">

    <header class="lunar-doc-header">
        <h1 class="lunar-doc-title">Lunar Base <span class="badge-version">{{ config('app.version') }}</span></h1>
        <p class="lunar-doc-lead">
            Guia definitivo de engenharia e referência do ecossistema <strong>Lunar Base</strong>.
            Projetado como um Starter Kit híbrido com características de CMS modular, o sistema gerencia seu comportamento através de estruturas declarativas, traits de expansão e injeção dinâmica de dependências.
        </p>
    </header>

    <div class="doc-quick-links">
        <a href="#arquitetura" class="quick-link">1. Arquitetura</a>
        <a href="#modulos" class="quick-link">2. Módulos</a>
        <a href="#configuracoes" class="quick-link">3. Configurações</a>
        <a href="#engenharia" class="quick-link">4. Engenharia</a>
        <a href="#seguranca" class="quick-link">5. Segurança</a>
    </div>

    <hr class="lunar-divider">

    <!-- SEÇÃO 1: ARQUITETURA -->
    <section id="arquitetura" class="lunar-doc-section">
        <h2 class="section-title">1. Paradigma de Arquitetura</h2>
        <p class="section-intro">
            A arquitetura do Lunar Base é <strong>Configuration-Driven</strong>. O comportamento do core é guiado por arquivos de configuração, o que elimina queries repetitivas e acelera o tempo de resposta (TTFB).
        </p>

        <div class="tech-grid">
            <div class="tech-card">
                <h4>Híbrido Declarativo</h4>
                <p>Menus e configurações são declarados em PHP estático, sendo 100% versionáveis via Git e rápidos no servidor.</p>
            </div>
            <div class="tech-card">
                <h4>Injeção via View Composers</h4>
                <p>Uso de <code>SiteComposer</code> para entregar dados globais (menus/footer) sem poluir Controllers com repetições.</p>
            </div>
            <div class="tech-card">
                <h4>Composição via Traits</h4>
                <p>Funcionalidades dinâmicas como <code>HasMeta</code> (JSON) e <code>HasReactions</code> injetadas sem complicar a herança.</p>
            </div>
            <div class="tech-card">
                <h4>Tema Adaptativo</h4>
                <p>Suporte nativo a modos <strong>Claro</strong> e <strong>Escuro</strong> com base nas preferências de acessibilidade do usuário.</p>
            </div>
        </div>
    </section>

    <!-- SEÇÃO 2: MÓDULOS -->
    <section id="modulos" class="lunar-doc-section">
        <h2 class="section-title">2. Estrutura e Módulos do Painel</h2>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-layout-dashboard class="lucid-icon" /> Dashboard</h3>
            <p class="item-text">Ponto de ancoragem para widgets de telemetria e relatórios gerenciais. Projetado para ser desacoplado e expansível com gráficos analíticos.</p>
        </div>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-file-text class="lucid-icon" /> Páginas</h3>
            <p class="item-text">
                CRUD para páginas estáticas ou dinâmicas. Suporta <strong>Namespaces lógicos</strong>, permitindo organizar URLs por contexto (ex: <code>/institucional/missao</code>) e seleção de layouts.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-files class="lucid-icon" /> Posts</h3>
            <p class="item-text">Focado em publicações cronológicas. Possui suporte nativo a agendamento, marcação de destaque (featured) e fixação permanente no topo (sticky).</p>
        </div>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-image class="lucid-icon" /> Mídia (Image Pipeline)</h3>
            <p class="item-text">
                Biblioteca integrada ao <strong>Intervention Image</strong>. Aplica automaticamente compressão, conversão WebP e geração de thumbnails inteligentes com foco no ponto central da imagem.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-form class="lucid-icon" /> Formulários</h3>
            <p class="item-text">
                Construtor de esquemas de captura de dados. Renderizados via Shortcode <code class="code-shortcode">[form slug="contato"]</code>. Possui gravação de respostas e notificações SMTP.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title"><x-lucide-list-checks class="lucid-icon" /> Logs de Auditoria</h3>
            <p class="item-text">Trilha de auditoria nativa. Captura todas as ações críticas na administração, indexando o usuário executor, IP, data e o registro afetado.</p>
        </div>
    </section>

    <!-- SEÇÃO 3: CONFIGURAÇÕES -->
    <section id="configuracoes" class="lunar-doc-section">
        <h2 class="section-title">3. Arquivos de Configuração (/config/*)</h2>
        <div class="tech-grid">
            <div class="tech-card">
                <h4>adminMenu.php</h4>
                <p>Define toda a estrutura lateral do painel, incluindo ícones, rotas e permissões de acesso.</p>
            </div>
            <div class="tech-card">
                <h4>settings.php</h4>
                <p>Atua como o "Schema" do painel geral. Define campos, validações e valores padrão de fallback.</p>
            </div>
            <div class="tech-card">
                <h4>site.php</h4>
                <p>Configurações estruturais do site público, como a árvore de navegação do header.</p>
            </div>
            <div class="tech-card">
                <h4>rolesPermissions.php</h4>
                <p>Define a matriz imutável de permissões por perfil (Admin, Editor, Autor).</p>
            </div>
        </div>
    </section>

    <!-- SEÇÃO 4: ENGENHARIA -->
    <section id="engenharia" class="lunar-doc-section">
        <h2 class="section-title">4. Engenharia e Helpers</h2>

        <div class="doc-item">
            <h3 class="item-title">SettingHelper & Precedência</h3>
            <p class="item-text">O método <code>setting('grupo.chave')</code> realiza o merge inteligente entre o banco de dados (valor atualizado) e o arquivo de config (valor padrão).</p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">SMTP Dinâmico</h3>
            <p class="item-text">As configurações de Mailer são injetadas em tempo de execução no <code>AppServiceProvider</code>, permitindo alterar servidores de e-mail sem reiniciar o servidor ou tocar no <code>.env</code>.</p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">Route Orchestrator</h3>
            <p class="item-text">
                Um resolvedor de rotas avançado que analisa a estrutura da URL para decidir qual Controller deve assumir a requisição, permitindo URLs amigáveis sem conflitos.
            </p>
        </div>
    </section>

    <!-- SEÇÃO 5: SEGURANÇA -->
    <section id="seguranca" class="lunar-doc-section">
        <h2 class="section-title">5. Camadas de Segurança</h2>
        <div class="tech-grid">
            <div class="tech-card">
                <h4>2FA Nativo</h4>
                <p>Autenticação de dois fatores via TOTP com chaves secretas criptografadas em repouso.</p>
            </div>
            <div class="tech-card">
                <h4>Cloudflare Turnstile</h4>
                <p>Proteção anti-spam e anti-bot no login via desafio invisível e acessível.</p>
            </div>
            <div class="tech-card">
                <h4>RBAC Hardcoded</h4>
                <p>Hierarquia de privilégios baseada em código, protegendo a aplicação contra mutações acidentais no banco.</p>
            </div>
        </div>
    </section>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/docs.css') }}">
@endpush
