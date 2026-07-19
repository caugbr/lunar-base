<?php

namespace Plugins\FAQ;

use Illuminate\Support\ServiceProvider;
use App\Helpers\ContentHelper;

class FAQServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    public function boot(): void
    {
        // 1. Carrega as views do plugin com namespace "faq"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'faq');

        // 2. Registra o Shortcode [faq slug="seu-slug"]
        ContentHelper::registerShortcode(
            'faq',
            function($attributes, $content) {
                $slug = $attributes['slug'] ?? null;
                if (!$slug) return '';

                // Busca a option específica do banco
                $optionData = getOption("faq_{$slug}");
                if (!$optionData) {
                    return '';
                }

                // Deserializa os dados salvos em JSON
                $faq = is_string($optionData) ? json_decode($optionData, true) : $optionData;

                if (empty($faq) || empty($faq['items'])) {
                    return '';
                }

                // Renderiza a view pública em acordeão
                if (view()->exists('faq::public.show')) {
                    return view('faq::public.show', compact('faq'))->render();
                }

                return '';
            },
            'Renderiza um set de perguntas e respostas',
            '[faq slug="meu-faq"]',
            [
                'slug' =>[
                    'label'       => 'Slug registrado para o FAQ',
                    'type'        => 'text',
                    'placeholder' => 'Slug do FAQ',
                ],
            ]
        );

        // 3. Injeta a aba FAQ no painel administrativo lateral
        \App\Support\AdminMenu::add([
            'label' => 'FAQ',
            'icon'  => 'file-question-mark',
            'route' => 'admin.faq.index',
            'active' => 'admin.faq.*',
            'permission' => 'manage-pages',
        ], 'Temas');
    }
}
