<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\DynamicRoutes;
use App\Http\Controllers\Public\PublicPageController;
use App\Http\Controllers\Public\PublicPostController;
use App\Models\Page;

class RouteOrchestratorController extends Controller
{
    /**
     * 1 segmento: ex: /{base} -> /blog (Listagem Geral do Blog)
     * 2 página sem base: ex: /{slug} -> /minha-pagina
     */
    public function handleOneSegment(Request $request, $base)
    {
        $blogBase = setting('navigation.blog_base', 'blog');

        if ($base === $blogBase) {
            return app(PublicPostController::class)->index($request);
        }

        $page = Page::where('slug', $base)
            ->whereNull('namespace')
            ->where('status', 'published')
            ->first();

        if ($page) {
            return app(PublicPageController::class)->show($base); // ou show($page->slug)
        }

        if ($view = DynamicRoutes::resolve($base)) {
            return $view;
        }

        abort(404);
    }

    /**
     * 2 segmentos:
     * ex: /pagina/sobre-nos         -> Página individual
     * ex: /institucional/sobre-nos  -> Página individual
     * ex: /blog/meu-primeiro-post   -> Post individual
     */
    public function handleTwoSegments($base, $slug)
    {
        $pagesBase = setting('navigation.pages_base', 'page');
        $postsBase = setting('navigation.posts_base', 'post');

        // === CASO 1: É uma Página com base fixa (/pagina/sobre-nos) ===
        if ($base === $pagesBase) {
            return app(PublicPageController::class)->show($slug);
        }

        // === CASO 2: Está na base do Blog (/blog/slug) ===
        if ($base === $postsBase) {
            return app(PublicPostController::class)->show($slug);
        }

        // === CASO 3: Página com namespace sem base (/institucional/missao) ===
        // $base pode ser um namespace, $slug é a página dentro dele
        if ($pagesBase === null) {
            $page = Page::where('slug', $slug)
                ->where('namespace', $base)
                ->where('status', 'published')
                ->first();

            if ($page) {
                return app(PublicPageController::class)->showNamespaced($base, $slug);
            }
        }

        if ($view = DynamicRoutes::resolve("{$base}/{$slug}")) {
            return $view;
        }

        abort(404);
    }

    /**
     * 3 segmentos:
     * ex: /pagina/institucional/missao -> Página dentro de Namespace
     * ex: /blog/categoria/laravel      -> Listagem de Posts filtrada por Taxonomia + Termo específico
     */
    public function handleThreeSegments($base, $namespace, $slug)
    {
        $pagesBase = setting('navigation.pages_base', 'page');
        $blogBase = setting('navigation.blog_base', 'blog');

        // Se for página de 3 segmentos (Ex: /pagina/institucional/missao)
        if ($base === $pagesBase) {
            return app(PublicPageController::class)->showNamespaced($namespace, $slug);
        }

        // Se for taxonomia (Ex: /blog/categoria/sem-categoria/)
        if ($base === $blogBase) {
            return app(PublicPostController::class)->byTerm($namespace, $slug);
        }

        if ($view = DynamicRoutes::resolve("{$base}/{$namespace}/{$slug}")) {
            return $view;
        }

        abort(404);
    }

    public function handleCatchAll(Request $request, $any)
    {
        $url = trim($any, '/');

        if ($view = DynamicRoutes::resolve($url)) {
            return $view;
        }

        abort(404);
    }
}
