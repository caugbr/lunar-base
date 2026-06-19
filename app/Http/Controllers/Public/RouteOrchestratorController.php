<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Public\PublicPageController;
use App\Http\Controllers\Public\PublicPostController;
use App\Models\Taxonomy;
use App\Models\Page;

class RouteOrchestratorController extends Controller
{
    /**
     * 1 segmento: ex: /{base} -> /blog (Listagem Geral do Blog)
     * 2 página sem base: ex: /{slug} -> /minha-pagina
     */
    public function handleOneSegment(Request $request, $base)
    {
        $blogBase = setting('permalinks.blog_base', 'blog');

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

        abort(404);
    }

    /**
     * 2 segmentos:
     * ex: /pagina/sobre-nos      -> Página individual
     * ex: /blog/meu-primeiro-post -> Post individual
     * ex: /blog/categoria        -> Listagem de Posts filtrada por Taxonomia (Sem termo específico)
     */
    // public function handleTwoSegments($base, $slug)
    // {
    //     $pagesBase = setting('permalinks.pages_base', 'page');
    //     $postsBase = setting('permalinks.posts_base', 'post');

    //     // === CASO 1: É uma Página Estática/Dinâmica ===
    //     if ($base === $pagesBase) {
    //         return app(PublicPageController::class)->show($slug);
    //     }

    //     // === CASO 2: Está na base do Blog ===
    //     if ($base === $postsBase) {
    //         // Verificamos no banco se esse $slug é, na verdade, uma Taxonomia cadastrada (ex: 'categoria', 'tag')
    //         $isTaxonomy = Taxonomy::where('slug', $slug)->exists();

    //         if ($isTaxonomy) {
    //             // Se for uma taxonomia, mandamos para o método de listagem por termo.
    //             // Como não foi passado um terceiro segmento (termo), passamos null ou tratamos no controller.
    //             // Nota: se o seu byTerm exigir o termo, podemos passar o próprio slug ou ajustar conforme seu controller espera.
    //             return app(PublicPostController::class)->byTerm($slug, null);
    //         }

    //         // Se NÃO for uma taxonomia, o comportamento padrão segue: é um post individual legítimo!
    //         return app(PublicPostController::class)->show($slug);
    //     }

    //     abort(404);
    // }
    public function handleTwoSegments($base, $slug)
    {
        $pagesBase = setting('permalinks.pages_base', 'page');
        $postsBase = setting('permalinks.posts_base', 'post');
            \Log::info('handleTwoSegments', ["namespace" => $base, "slug" => $slug, "pagesBase" => $pagesBase, "postsBase" => $postsBase]);

        // === CASO 1: É uma Página com base fixa (/pagina/sobre-nos) ===
        if ($base === $pagesBase) {
            return app(PublicPageController::class)->show($slug);
        }

        // === CASO 2: Está na base do Blog (/blog/...) ===
        if ($base === $postsBase) {
            $isTaxonomy = Taxonomy::where('slug', $slug)->exists();

            if ($isTaxonomy) {
                return app(PublicPostController::class)->byTerm($slug, null);
            }

            return app(PublicPostController::class)->show($slug);
        }

        // === CASO 3: Página com namespace sem base (/institucional/missao) ===
        // $base pode ser um namespace, $slug é a página dentro dele
        if ($pagesBase === null) {
            \Log::info('sem base', ["namespace" => $base, "slug" => $slug]);
            $page = Page::where('slug', $slug)
                ->where('namespace', $base)
                ->where('status', 'published')
                ->first();

            if ($page) {
                return app(PublicPageController::class)->showNamespaced($base, $slug);
            }
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
        $pagesBase = setting('permalinks.pages_base', 'page');
        $blogBase = setting('permalinks.blog_base', 'post');

        // Se for página de 3 segmentos (Ex: /pagina/institucional/missao)
        if ($base === $pagesBase) {
            return app(PublicPageController::class)->showNamespaced($namespace, $slug);
        }

        // Se for página de 3 segmentos (Ex: /pagina/institucional/missao)
        if ($base === $blogBase) {
            return app(PublicPostController::class)->byTerm($namespace, $slug);
        }

        abort(404);
    }
}
