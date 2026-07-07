@props([
    "title" => "Subpáginas",
    "title_icon" => "list-tree",
    "item_icon" => "chevron-right",
    "page" => null
])

@php
    // 1. Resolve os parâmetros de rota de forma dinâmica
    $route = request()->route();
    $baseParam = $route?->parameter('base');
    $namespaceParam = $route?->parameter('namespace');
    $slugParam = $route?->parameter('slug');

    // Carrega a configuração da base de páginas do banco
    $pagesBase = setting('navigation.pages_base', 'page');
    $blogBase = setting('navigation.blog_base', 'blog');

    // 2. A página atual pode vir da prop, do compartilhamento ou resolvida pela rota
    $currentPage = $page ?? view()->shared('page');

    if (!$currentPage) {
        if ($slugParam) {
            // 💡 Caso 1: 3 segmentos (Ex: /page/tutorial-x/item-a)
            if ($namespaceParam) {
                if ($baseParam === $pagesBase) {
                    $currentPage = \App\Models\Page::where('slug', $slugParam)
                        ->where('namespace', $namespaceParam)
                        ->where('status', 'published')
                        ->first();
                }
            } else {
                // 💡 Caso 2: 2 segmentos
                if ($baseParam === $pagesBase) {
                    // Ex: /page/sobre-nos (Sem namespace)
                    $currentPage = \App\Models\Page::where('slug', $slugParam)
                        ->whereNull('namespace')
                        ->where('status', 'published')
                        ->first();
                } elseif (empty($pagesBase)) {
                    // Ex: /institucional/missao (Quando o prefixo 'pages_base' é vazio,
                    // o primeiro parâmetro 'base' atua como o namespace e o segundo como 'slug')
                    $currentPage = \App\Models\Page::where('slug', $slugParam)
                        ->where('namespace', $baseParam)
                        ->where('status', 'published')
                        ->first();
                }
            }
        } elseif ($baseParam) {
            // 💡 Caso 3: 1 segmento
            // Ex: /sobre-nos (Página sem base e sem namespace)
            // Primeiro certificamos que não é o blog (caso o blog_base seja o mesmo do baseParam)
            if ($baseParam !== $blogBase) {
                $currentPage = \App\Models\Page::where('slug', $baseParam)
                    ->whereNull('namespace')
                    ->where('status', 'published')
                    ->first();
            }
        }
    }

    // 3. Busca apenas as subpáginas publicadas associadas a esta página
    $subpages = $currentPage ? $currentPage->children()->published()->get() : collect();
@endphp

@if($currentPage && $subpages->isNotEmpty())

@once
<style>
    .subpages-navigation {
        margin: 2rem 0;
        padding: 1.5rem;
        background: var(--color-bg-dark, #12152b);
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        border-radius: 12px;
    }

    .subpages-navigation h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        color: var(--color-primary, #c8b6ff);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .subpages-navigation h3 .lucid-icon {
        width: 18px;
        height: 18px;
    }

    .subpages-navigation ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }

    .subpages-navigation li {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .subpages-navigation li .lucid-icon {
        width: 14px;
        height: 14px;
        color: var(--color-text-muted, #8a87a8);
        flex-shrink: 0;
    }

    .subpages-navigation a {
        font-weight: 500;
        color: var(--color-text, #e8e6f0);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .subpages-navigation a:hover {
        color: var(--color-primary, #c8b6ff);
    }
</style>
@endonce

<div class="subpages-navigation">
    <h3>
        @if($title_icon)
        <x-dynamic-component component="lucide-{{ $title_icon }}" class="lucid-icon" />
        @endif
        {{ $title }}
    </h3>
    <ul>
        @foreach($subpages as $subpage)
            <li>
                @if($item_icon)
                <x-dynamic-component component="lucide-{{ $item_icon }}" class="lucid-icon" />
                @endif
                <a href="{{ $subpage->url }}">
                    {{ $subpage->title }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endif
