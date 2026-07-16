<?php

namespace Plugins\Banners;

use Illuminate\Support\ServiceProvider;
use App\Support\HookManager;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Schema;
use Plugins\Banners\Models\Banner;

class BannersServiceProvider extends ServiceProvider
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
        // 1. Carrega views com namespace "banners"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'banners');

        // 2. Carrega migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // 3. REGISTRO DINAMICO DE HOOKS BASEADO NO BANCO
        if (!app()->runningInConsole() && Schema::hasTable('banners')) {
            $activeBanners = Banner::where('is_active', true)
                ->whereNotNull('hook')
                ->where('hook', '!=', '')
                ->with('image')
                ->get();

            foreach ($activeBanners as $banner) {
                HookManager::register($banner->hook, function($params) use ($banner) {
                    return view('banners::public.banner', [
                        'banner' => $banner,
                        'class' => $banner->class,
                    ])->render();
                }, 'Banners Plugin');
            }
        }

        // 4. REGISTRO DO SHORTCODE
        ContentHelper::registerShortcode('banner', function($attributes, $content) {
            $slug = $attributes['slug'] ?? null;
            $class = $attributes['class'] ?? null;

            if (!$slug) {
                return '<!-- Erro: [banner] requer atributo "slug" -->';
            }

            $banner = Banner::where('slug', $slug)
                ->where('is_active', true)
                ->with('image')
                ->first();

            if (!$banner) {
                return '';
            }

            return view('banners::public.banner', [
                'banner' => $banner,
                'class' => $class ?? $banner->class,
            ])->render();
        });

        // 5. Helper global
        if (!function_exists('renderBanner')) {
            function renderBanner(string $slug, ?string $class = null): string
            {
                $banner = Banner::where('slug', $slug)
                    ->where('is_active', true)
                    ->with('image')
                    ->first();

                if (!$banner) {
                    return '';
                }

                return view('banners::public.banner', [
                    'banner' => $banner,
                    'class' => $class ?? $banner->class,
                ])->render();
            }
        }

        // 6. Injeta links no menu lateral administrativo
        \App\Support\AdminMenu::add([
            'label' => 'Banners',
            'icon'  => 'square',
            'route' => 'admin.banners.index',
            'active' => 'admin.banners.*',
            'role' => 'admin',
        ], 'Taxonomias');

        \App\Support\AdminMenu::addSubItem('Banners', [
            'label' => 'Novo Banner',
            'icon'  => 'square-plus',
            'route' => 'admin.banners.create',
            'active' => 'admin.banners.create',
            'role' => 'admin',
        ]);
    }
}
