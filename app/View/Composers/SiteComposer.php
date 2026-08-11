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
            // 1. Filtro de Visibilidade por Domínio/Namespace
            if (!$this->isItemVisibleForCurrentDomain($item)) {
                continue;
            }

            // 2. Trata rotas nomeadas
            if (!empty($item['route'])) {
                $item['href'] = route($item['route']);
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
                continue;
            }

            // 3. Trata caminhos/URLs diretas
            if (!empty($item['path'])) {
                $item['href'] = url($item['path']);
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
                continue;
            }

            // 4. Trata Páginas por Slug (Busca Inteligente)
            $page = false;
            if (!empty($item['slug'])) {
                // Se o item do menu especificou um namespace explicitamente no array, usa ele
                if (!empty($item['namespace'])) {
                    $page = Page::published()
                        ->where('slug', $item['slug'])
                        ->where('namespace', $item['namespace'])
                        ->first();
                } else {
                    // Caso contrário, busca no namespace do domínio atual
                    $currentNs = currentNamespace();

                    if ($currentNs && $currentNs !== 'default') {
                        $page = Page::published()
                            ->where('slug', $item['slug'])
                            ->where('namespace', $currentNs)
                            ->first();
                    }

                    // Fallback: Se não encontrou no namespace atual (ou está no principal),
                    // busca a página global (sem namespace)
                    if (!$page) {
                        $page = Page::published()
                            ->where('slug', $item['slug'])
                            ->where(function ($q) {
                                $q->whereNull('namespace')->orWhere('namespace', '');
                            })
                            ->first();
                    }
                }
            }

            if ($page) {
                $item['href'] = $page->url;
                $item['current_class'] = $item['href'] === $currentUrl ? ' active' : '';
                $menu[] = $item;
            }
        }

        return $menu;
    }

    /**
     * Verifica se o item do menu deve ser exibido no domínio/namespace atual.
     */
    protected function isItemVisibleForCurrentDomain(array $item): bool
    {
        $domains = $item['domains'] ?? ['*'];

        // Se for informado como string separada por vírgula, converte para array
        if (is_string($domains)) {
            $domains = array_map('trim', explode(',', $domains));
        }

        // Se estiver vazio ou contiver '*', está visível em TODOS os domínios
        if (empty($domains) || in_array('*', $domains, true)) {
            return true;
        }

        $currentHost      = currentSiteDomain(); // Ex: 'parceiros.lunarapps.com.br' ou host atual
        $currentNamespace = currentNamespace();  // Ex: 'parceiros' ou 'default'

        foreach ($domains as $domain) {
            $domain = trim($domain);

            // Match direto pelo host do domínio
            if ($domain === $currentHost) {
                return true;
            }

            // Match pelo namespace associado ao domínio
            if ($domain === $currentNamespace) {
                return true;
            }

            // Aliases comuns para o site principal/padrão
            if (in_array($domain, ['main', 'default', 'primary'], true) && !isExtraDomain()) {
                return true;
            }
        }

        return false;
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
