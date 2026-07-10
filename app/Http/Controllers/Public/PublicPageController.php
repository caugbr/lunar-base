<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Helpers\ContentHelper;

class PublicPageController extends Controller
{
    /**
     * Exibe uma página a partir do slug.
     */
    public function show($slug)
    {
        // 1. Tenta página global (sem widget)
        $page = Page::where('slug', $slug)
            ->whereNull('namespace')
            ->where('status', 'published')
            ->first();

        if ($page) {
            return $this->renderPage($page);
        }

        abort(404);
    }

    /**
     * Exibe uma página com namespace.
     * URL: /page/namespace/page_slug
     */
    public function showNamespaced($namespace, $slug)
    {

        $page = Page::where('slug', $slug)
            ->where('namespace', $namespace)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->renderPage($page);
    }

    /**
     * Renderiza a página com os elementos do site.
     */
    protected function renderPage(Page $page)
    {
        // 💡 Injeta o modelo de página diretamente na rota ativa
        request()->route()->setParameter('resolved_entity', $page);

        $page->content = ContentHelper::parseShortcodes($page->content);

        $page->load(['children' => fn($q) => $q->published()]);

        $templateName = $page->template ?? config('pageTemplates.default');
        $template = 'public.page-templates.' . $templateName;

        return view($template, compact('page'));
    }
}
