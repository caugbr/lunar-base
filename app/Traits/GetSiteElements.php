<?php

namespace App\Traits;

use App\Models\Page;
use Illuminate\Support\Facades\Config;

trait GetSiteElements
{
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
            if (!empty($item['slug']) && !empty($item['namespace'])) {
                $page = Page::published()
                    ->where('slug', $item['slug'])
                    ->where('namespace', $item['namespace'])
                    ->first();
            } else {
                if (!empty($item['slug'])) {
                    $page = Page::published()->where('slug', $item['slug'])->first();
                }
            }

            if ($page) {
                $item['href'] = $page?->url;
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
            }
        }

        return $menu;
    }

    // Retorna link para páginas com slugs 'termos-de-uso' e 'politica-de-privacidade'
    // sem namespaces associados (termos e política do site) para o footer
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
