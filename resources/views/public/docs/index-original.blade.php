@extends('public.site-layout')

@section('title', 'Documentação Técnica de Arquitetura — Lunar Base')
@section('meta_description', 'Guia de referência detalhado sobre o ecossistema, helpers, traits e configurações do Lunar Base Starter Kit.')

@section('content')
<div class="lunar-logo">
    <img src="{{ asset('images/lunar-base.png') }}" alt="Lunar Base - Laravel Starter Kit">
</div>

<div class="lunar-doc-container">

    <header class="lunar-doc-header">
        <h1 class="lunar-doc-title">Lunar Base <span class="badge-version">{{ config('app.version') }}</span></h1>
        <p class="lunar-doc-lead">
            Guia de engenharia e referência do ecossistema Lunar Base. Projetado como um Starter Kit híbrido com
            características de CMS modular, o sistema gerencia seu comportamento operacional através de estruturas
            declarativas em arquivos de configuração locais, traits de expansão e helpers de contexto.
        </p>
    </header>

    <hr class="lunar-divider">

    <section class="lunar-doc-section">
        <h2 class="section-title">1. Estrutura e Módulos do Painel Administrativo</h2>
        <p class="section-intro">
            A interface administrativa organiza suas permissões, barramentos de rota e renderização a partir do
            mapeamento centralizado no arquivo de configuração <code class="code-filepath">/config/adminMenu.php</code>.
        </p>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-layout-dashboard class="lucide-icon" /> Dashboard
            </h3>
            <p class="item-text">
                Fornecido como uma view desacoplada e limpa no core administrativo. O componente serve como um ponto de
                ancoragem livre, projetado para receber futuros widgets de telemetria, dashboards analíticos, gráficos
                customizados do Chart.js ou blocos de relatórios gerenciais específicos do escopo de cada projeto.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-file class="lucide-icon" /> Páginas
            </h3>
            <p class="item-text">
                Módulo responsável pelo CRUD estrutural de páginas estáticas ou dinâmicas no front-end. Suporta uploads
                de miniaturas (thumbnails) e a seleção dinâmica de layouts mapeados em código no arquivo <code
                    class="code-filepath">/config/pageTemplates.php</code> (como as opções <em>Site page</em>,
                <em>Default</em>, <em>Fullwidth</em> e <em>Left Sidebar</em>). Permite a segmentação de caminhos na URL
                por meio de isolamento em namespaces lógicos configuráveis no momento da criação do registro.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-files class="lucide-icon" /> Posts
            </h3>
            <p class="item-text">
                Gerenciador de publicações cronológicas focado em fluxos de blog ou notícias. Admite amarração a
                templates declarados em <code class="code-filepath">/config/postTemplates.php</code> e traz, em nível de
                banco de dados, propriedades nativas para agendamentos por data futura, marcação de relevância
                (featured) e fixação permanente no topo de listagens (sticky).
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-image class="lucide-icon" /> Mídia
            </h3>
            <p class="item-text">
                Biblioteca central de uploads e gerenciamento de arquivos integrada ao disco local da aplicação (<code
                    class="code-filepath">storage/app/public</code>). Seu comportamento responde às diretrizes globais
                do sistema, aplicando redimensionamento automático de thumbnails e rotinas de compressão de imagens de
                acordo com os parâmetros de qualidade estipulados via painel administrativo.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-tags class="lucide-icon" /> Taxonomias
            </h3>
            <p class="item-text">
                Abstração de agrupamentos lógicos relacionais. Permite a criação de termos flexíveis que podem ser
                associados concorrentemente tanto a Páginas quanto a Posts, facilitando rotinas de filtragem,
                categorização e organização de coleções de conteúdo no front-end.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-form class="lucide-icon" /> Formulários
            </h3>
            <p class="item-text">
                Camada isolada para concepção de estruturas de captura de dados. Os formulários geram instâncias
                reutilizáveis renderizadas no cliente através do interpretador de shortcodes usando a sintaxe <code
                    class="code-shortcode">[form slug="meu-formulario"]</code>. Possui controller unificado para
                recepção do payload, validação, gravação imutável no banco de dados, disparo opcional de notificações
                SMTP configuradas via painel e devolução de feedbacks ao usuário final na mesma view de origem.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-users class="lucide-icon" /> Usuários
            </h3>
            <p class="item-text">
                Interface de gerenciamento de identidades e credenciais administrativas. Garante a manutenção,
                rotatividade de acessos e a vinculação direta das contas de usuários locais aos papéis de privilégios e
                permissões ativos na aplicação.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-user-key class="lucide-icon" /> Permissões (RBAC)
            </h3>
            <p class="item-text">
                Sistema de controle de acesso baseado em papéis (Role-Based Access Control). <strong>Nota de
                    Arquitetura:</strong> Os perfis e as permissões não realizam persistência no banco de dados; toda a
                sua árvore de herança e capacidades é declarada de forma estática e segura em código no arquivo <code
                    class="code-filepath">/config/rolesPermissions.php</code>. A tela administrativa do painel opera em
                modo estritamente analítico (Read-Only) para auditoria visual.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-settings class="lucide-icon" /> Configurações
            </h3>
            <p class="item-text">
                Painel dinâmico para parametrização de variáveis globais estruturado sob o mapeamento de definições
                contido em <code class="code-filepath">/config/settings.php</code>. Controla abas modulares de metadados
                gerais (SEO), regras de navegação da admin (como persistência de buscas e CAPTCHAs), configurações de
                compressão de mídia, URLs de canais sociais, bases de permalinks e parâmetros de infraestrutura para
                servidores SMTP de e-mail.
            </p>
        </div>

        <div class="doc-item">
            <h3 class="item-title">
                <x-lucide-list-checks class="lucide-icon" /> Logs de Ações
            </h3>
            <p class="item-text">
                Trilha de auditoria (Audit Trail) nativa do sistema. Registra logs operacionais capturando eventos
                cruciais da aplicação, indexando o usuário executor, a tipologia da ação (criação, mutação ou exclusão
                de registros) e o registro cronológico exato do evento.
            </p>
        </div>
    </section>

    <hr class="lunar-divider">

    <section class="lunar-doc-section">
        <h2 class="section-title">2. Estrutura de Arquivos de Configuração (/config/*)</h2>
        <p class="section-intro">
            A arquitetura do Lunar Base utiliza arquivos de configuração declarativos para guiar o comportamento do
            core, eliminando queries repetitivas no banco de dados e agilizando o tempo de resposta (TTFB):
        </p>

        <div class="doc-item">
            <h4 class="tech-subtitle">adminMenu.php</h4>
            <p class="item-text">Controla o menu administrativo lateral esquerdo. Define os rótulos (labels), ícones
                Lucide, rotas nomeadas de destino, regras de ativação de estado ativo em lote (padrão regex como <code
                    class="code-inline">admin.pages.*</code>) e barreiras de proteção atreladas a papéis ou permissões
                específicas.</p>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">rolesPermissions.php</h4>
            <p class="item-text">Repositório estático que define os perfis de acesso do sistema (<code
                    class="code-inline">roles</code>) — como Administrador, Editor, Autor e Assinante — mapeando a
                matriz exata de permissões (<code class="code-inline">permissionsByRole</code>) e os rótulos de
                visualização humana estruturados em grupos amigáveis no painel.</p>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">settings.php</h4>
            <p class="item-text">Atua como o Schema do painel de configurações gerais. Cada grupo (Geral, Mídia, Redes
                Sociais, SMTP) possui um array declarativo de campos, definindo chaves físicas, tipos de dados de
                renderização (text, textarea, switch, select), valores padrões (fallback) e a ordem sequencial de
                exibição.</p>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">pageTemplates.php & postTemplates.php</h4>
            <p class="item-text">Arquivos responsáveis por listar os arquivos de template Blade utilizáveis pelas visões
                públicas. Permitem associar arquivos físicos mapeados dentro do diretório de views aos nomes amigáveis
                exibidos nos seletores da administração.</p>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">site.php</h4>
            <p class="item-text">Concentra as diretivas estruturais exclusivas do ecossistema público do site. O menu
                principal de navegação do cabeçalho da aplicação (Header) responde dinamicamente à matriz descrita no nó
                <code class="code-inline">config('site.mainMenu')</code>.</p>
        </div>
    </section>

    <hr class="lunar-divider">

    <section class="lunar-doc-section">
        <h2 class="section-title">3. Barramento de APIs, Helpers Globais e Traits</h2>
        <p class="section-intro">
            A manipulação de estados do core e a renderização de elementos dinâmicos no front-end contam com componentes
            utilitários globais:
        </p>

        <div class="doc-item">
            <h4 class="tech-subtitle">SettingHelper (Notação de Ponto)</h4>
            <p class="item-text">
                O arquivo de suporte global expõe funções que realizam a mesclagem automática entre os dados dinâmicos
                gravados no banco e os fallbacks de configuração estruturados. A assinatura do método adota o formato
                <code class="code-inline">grupo.chave</code> para isolamento seguro:
            </p>
            <div class="code-block">
                <pre><code>// Resgata o valor atualizado do banco de dados. Caso não exista, adota o default do arquivo config
$siteName = setting('general.site_name');

// Retorna um mapa associativo chave => valor contendo todas as variáveis de um determinado grupo
$smtpConfig = settingsGroup('mail');

// Coleta a totalidade de configurações integradas de todos os grupos do sistema
$allSystemSettings = settingsAll();

// Isola e retorna unicamente o valor original estático definido no config/settings.php
$fallbackTheme = settingDefault('general.site_theme');</code></pre>
            </div>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">ContentHelper & Trait Shortcodes</h4>
            <p class="item-text">
                O processamento de blocos textuais e renderizações dinâmicas em tempo de execução de visões é
                orquestrado estaticamente por <code class="code-inline">ContentHelper::parseShortcodes($content)</code>.
                O método intercepta o HTML bruto gerado por editores rich-text e delega a resolução de tags à trait de
                Shortcodes.
            </p>
            <p class="item-text">
                A inclusão de novos padrões de tags na aplicação é feita adicionando novos métodos privados na trait
                seguindo o padrão camelCase <code class="code-inline">render{NomeDoShortcode}</code>:
            </p>
            <div class="code-block">
                <pre><code>// Estrutura de resolução acionada automaticamente para a tag [form slug="..."]
private static function renderForm($attributes) {
    $slug = $attributes['slug'] ?? null;
    if (!$slug) return '';

    $form = Form::active()->where('slug', $slug)->first();
    return view('forms.embed', ['form' => $form])->render();
}</code></pre>
            </div>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">RolePermissionHelper</h4>
            <p class="item-text">
                Disponibiliza validadores booleanos globais rápidos para checagem estrutural de acessos diretamente em
                controladores ou blocos condicionais de visões Blade:
            </p>
            <div class="code-block">
                <pre><code>// Restringe a execução do bloco de código a um papel específico
if (isRole('admin')) {
    // Escopo de administração total
}

// Avalia a capacidade pontual do usuário logado contra a matriz estática do config
if (userCan('manage-pages')) {
    // Permissão concedida para controle do CRUD de páginas
}</code></pre>
            </div>
        </div>

        <div class="doc-item">
            <h4 class="tech-subtitle">Orquestrador de Permalinks e Roteamento Híbrido</h4>
            <p class="item-text">
                Para evitar conflitos de barramento e garantir total flexibilidade, as requisições públicas
                não-estáticas são capturadas de forma genérica no fim do arquivo de rotas e centralizadas no <code
                    class="code-inline">RouteOrchestratorController</code>. Este componente atua como um resolvedor
                lógico em tempo de execução: ele analisa os segmentos textuais da URL contra as chaves dinâmicas salvas
                no banco de dados (<code class="code-inline">permalinks.pages_base</code>, <code
                    class="code-inline">permalinks.posts_base</code> e <code
                    class="code-inline">permalinks.blog_base</code>) e despacha a requisição com suas respectivas
                dependências para o controller especialista correto, disparando o <code
                    class="code-inline">abort(404)</code> imediatamente caso nenhuma definição coincida.
            </p>
            <div class="code-block">
                <pre><code>// Estrutura estática de captura sequencial mapeada no fim do arquivo routes/web.php
// Suporta perfeitamente o empacotamento nativo do 'route:cache' do Laravel

// Casos com 3 segmentos (Ex: Páginas em Namespaces OU Posts filtrados por Taxonomia/Termo)
Route::get('/{base}/{namespace}/{slug}', [RouteOrchestratorController::class, 'handleThreeSegments'])->name('dynamic.three.segments');

// Casos com 2 segmentos (Ex: Resolução de Página individual OU Post individual via SLUG único)
Route::get('/{base}/{slug}', [RouteOrchestratorController::class, 'handleTwoSegments'])->name('dynamic.two.segments');

// Casos com 1 segmento (Ex: Ponto de entrada e listagem cronológica principal do Blog)
Route::get('/{base}', [RouteOrchestratorController::class, 'handleOneSegment'])->name('dynamic.one.segment');</code></pre>
            </div>
        </div>
    </section>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/docs.css') }}">
@endpush
