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
        $theme = setting('site_theme') ?? '';
        if ($theme) {
            $theme = " data-theme=\"{$theme}\"";
        }
        $view->with('menu', $this->buildMenu());
        $view->with('termsAndPrivacy', $this->getTermsAndPrivacyPages());
        $view->with('footerText', $this->getFooterText());
        $view->with('theme', $theme);
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
        $ids = [setting('general.privacy_page_id'), setting('general.terms_page_id')];
        $pages = Page::published()
            ->whereNull('namespace')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return [
            'privacy' => $pages[$ids[0]] ?? null,
            'terms'   => $pages[$ids[1]] ?? null,
        ];
    }

    protected function getFooterText(): string
    {
        $text = strip_tags(setting('general.footer_text', ''));

        $text = preg_replace(
            '/(https?:\/\/[^\s<]+[^\s.,])+/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );
        $text = preg_replace(
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '<a href="mailto:$1">$1</a>',
            $text
        );

        return $text;
    }
}
