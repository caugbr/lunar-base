@props([
    'page' => null,
    'post' => null,
    'icon' => ''
])

@php
    $crumbs = [];

    // 💡 Toda trilha começa pelo Início (Home)
    $crumbs[] = ['title' => 'Início', 'url' => route('home')];

    // 1. Tenta obter o $page e $post de forma flexível (passado ou compartilhado)
    $currentPage = $page ?? view()->shared('page');
    $currentPost = $post ?? view()->shared('post');

    // Carrega as configurações de bases dos permalinks
    $pagesBase = setting('permalinks.pages_base', 'page');
    $postsBase = setting('permalinks.posts_base', 'post');
    $blogBase = setting('permalinks.blog_base', 'blog');

    // 2. Auto-resolução por rota para evitar o fallback de segmentos em páginas dinâmicas
    if (!$currentPage && !$currentPost) {
        $route = request()->route();
        $slug = $route?->parameter('slug') ?? $route?->parameter('base');

        if ($slug) {
            $currentPost = \App\Models\Post::where('slug', $slug)->first();

            if (!$currentPost) {
                $namespace = $route?->parameter('namespace');
                if ($namespace) {
                    $currentPage = \App\Models\Page::where('slug', $slug)->where('namespace', $namespace)->first();
                } else {
                    $currentPage = \App\Models\Page::where('slug', $slug)->whereNull('namespace')->first();
                }
            }
        }
    }

    // 💡 DETECÇÃO DE ROTA DE TAXONOMIA:
    // Verifica se estamos na rota de listagem de posts por termo de taxonomia (ex: /blog/categoria/sem-categoria)
    $isTaxonomyTermRoute = false;
    $currentTaxonomy = null;
    $currentTerm = null;

    $route = request()->route();
    $routeBase = $route?->parameter('base');
    $routeNamespace = $route?->parameter('namespace');
    $routeSlug = $route?->parameter('slug');

    if ($routeBase === $blogBase && $routeNamespace && $routeSlug) {
        $currentTaxonomy = \App\Models\Taxonomy::where('slug', $routeNamespace)->first();
        if ($currentTaxonomy) {
            $currentTerm = \App\Models\Term::where('slug', $routeSlug)
                ->where('taxonomy_id', $currentTaxonomy->id)
                ->first();
            if ($currentTerm) {
                $isTaxonomyTermRoute = true;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CENÁRIO A: Estamos em uma Página Dinâmica?
    // ─────────────────────────────────────────────────────────────────────────
    if ($currentPage) {
        // Se a página tem um namespace, adicionamos apenas como um indicador de texto simples (sem link)
        if ($currentPage->namespace) {
            $crumbs[] = [
                'title' => ucfirst(str_replace('-', ' ', $currentPage->namespace)),
                'url' => null // Sem link, apenas indicador!
            ];
        }

        $ancestors = [];
        $parent = $currentPage->parent;

        // Sobe recursivamente buscando os pais da página atual (que são páginas de verdade e possuem .url)
        while ($parent) {
            array_unshift($ancestors, ['title' => $parent->title, 'url' => $parent->url]);
            $parent = $parent->parent;
        }

        $crumbs = array_merge($crumbs, $ancestors);
        $crumbs[] = ['title' => $currentPage->title, 'url' => null];

    // ─────────────────────────────────────────────────────────────────────────
    // CENÁRIO B: Estamos na Rota de Listagem por Termo (ex: /blog/categoria/sem-categoria)
    // ─────────────────────────────────────────────────────────────────────────
    } elseif ($isTaxonomyTermRoute) {
        // Link real e clicável para o Blog
        $crumbs[] = ['title' => 'Blog', 'url' => url('/' . $blogBase)];

        // Exibe de forma combinada e acessível como o último item (ativo, sem link)
        $crumbs[] = [
            'title' => $currentTaxonomy->name . ': ' . $currentTerm->name, // Ex: "Categoria: Sem categoria"
            'url' => null
        ];

    // ─────────────────────────────────────────────────────────────────────────
    // CENÁRIO C: Estamos em um Post do Blog?
    // ─────────────────────────────────────────────────────────────────────────
    } elseif ($currentPost) {
        // Link real e clicável do Blog
        $crumbs[] = ['title' => 'Blog', 'url' => url('/' . $blogBase)];

        // Adiciona apenas o post ativo no fim (sem categorias intermediárias)
        $crumbs[] = ['title' => $currentPost->title, 'url' => null];

    // ─────────────────────────────────────────────────────────────────────────
    // CENÁRIO D: Rota Estática do Sistema (ex: /docs, /login) ou Fallback genérico
    // ─────────────────────────────────────────────────────────────────────────
    } else {
        $segments = request()->segments();
        $urlAccumulated = '';

        foreach ($segments as $segment) {
            $urlAccumulated .= '/' . $segment;

            // Se o segmento for igual ao prefixo de páginas (ex: 'page'), ignoramos totalmente!
            if ($segment === $pagesBase) {
                continue;
            }

            // Se o segmento for o de posts (ex: 'post'), substituímos por 'Blog' e mandamos para o blog real
            if ($segment === $postsBase) {
                $crumbs[] = ['title' => 'Blog', 'url' => url('/' . $blogBase)];
                continue;
            }

            // Se o segmento for um namespace cadastrado, mostramos como indicador simples de texto (sem link)
            $isNamespace = \App\Models\Page::where('namespace', $segment)->exists();

            // Tradução de rotas estáticas comuns
            $title = match ($segment) {
                'docs' => 'Documentação',
                'login' => 'Acesso',
                'profile' => 'Meu Perfil',
                default => ucfirst(str_replace('-', ' ', $segment))
            };

            $crumbs[] = [
                'title' => $title,
                'url' => ($segment === end($segments) || $isNamespace) ? null : url($urlAccumulated)
            ];
        }
    }
@endphp

<!-- Semântica HTML de Acessibilidade -->
<nav aria-label="breadcrumb" class="lunar-breadcrumbs">
    {{-- <x-lucide-milestone class="lucid-icon" /> --}}
    @if($icon)
    <x-dynamic-component :component="'lucide-' . $icon" class="lucid-icon" />
    @endif
    <ol class="breadcrumbs-list">
        @foreach($crumbs as $crumb)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" {{ $loop->last ? 'aria-current="page"' : '' }}>
                @if($crumb['url'] && !$loop->last)
                    <a href="{{ $crumb['url'] }}">{{ $crumb['title'] }}</a>
                @else
                    <span>{{ $crumb['title'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@once
@push('footer-styles')
<style>
    .lunar-breadcrumbs {
        /* margin-bottom: 2rem; */
        transform: translateY(-100%);
        font-size: 0.9rem;
        background-color: var(--color-bg-dark);
        border-radius: 14px;
        padding: 4px 14px;
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .lunar-breadcrumbs .lucid-icon {
        display: inline-block;
        width: 16px;
        height: 16px;
    }
    .breadcrumbs-list {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding-left: 0;
        margin: 0;
        color: var(--color-text-muted, #8a87a8);
    }
    .breadcrumb-item {
        display: inline-flex;
        align-items: center;
    }
    .breadcrumb-item a {
        color: var(--color-text-muted, #8a87a8);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .breadcrumb-item a:hover {
        color: var(--color-primary, #c8b6ff);
    }
    .breadcrumb-item:not(:last-child)::after {
        content: "/";
        margin: 0 0.5rem;
        opacity: 0.5;
        font-size: 0.8rem;
    }
    .breadcrumb-item.active {
        color: var(--color-text, #e8e6f0);
        font-weight: 500;
    }
</style>
@endpush
@endonce
