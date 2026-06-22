<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Page;
use Illuminate\Support\Facades\Config;

class SiteComposer
{
    /**
     * Este método é chamado automaticamente pelo Laravel antes da view ser exibida.
     */
    public function compose(View $view)
    {
        $view->with('menu', $this->buildMenu());
        $view->with('termsAndPrivacy', $this->getTermsAndPrivacyPages());
    }

    protected function buildMenu(): array
    {
        $rawMenu = Config::get('site.mainMenu', []);
        $menu = [];
        $currentUrl = request()->url();

        foreach ($rawMenu as $item) {
            if (!empty($item['route'])) {
                $item['href'] = route($item['route']);
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
                continue;
            }

            if (!empty($item['path'])) {
                $item['href'] = url($item['path']);
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
                continue;
            }

            $page = false;
            if (!empty($item['slug'])) {
                $query = Page::published()->where('slug', $item['slug']);
                if (!empty($item['namespace'])) {
                    $query->where('namespace', $item['namespace']);
                }
                $page = $query->first();
            }

            if ($page) {
                $item['href'] = $page->url;
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
            }
        }

        return $menu;
    }

    protected function getTermsAndPrivacyPages(): array
    {
        $slugs = ['termos-de-uso', 'politica-de-privacidade'];
        $pages = Page::published()
            ->whereNull('namespace')
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        return [
            'terms'   => $pages['termos-de-uso'] ?? null,
            'privacy' => $pages['politica-de-privacidade'] ?? null
        ];
    }
}
