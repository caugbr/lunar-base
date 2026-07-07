<?php

namespace Plugins\Menus;

use Illuminate\Support\ServiceProvider;
use App\Support\HookManager;
use Illuminate\Support\Facades\Schema;
use Plugins\Menus\Models\Menu;

class MenusServiceProvider extends ServiceProvider
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
        // 1. Carrega as views do plugin com namespace "menus"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'menus');

        // 2. REGISTRO DINÂMICO DE CALLBACKS BASEADO NO BANCO DE DADOS
        if (!app()->runningInConsole() && Schema::hasTable('menus')) {

            // Busca apenas os menus que possuem associação com ganchos (coluna hook preenchida)
            $menusWithHooks = Menu::whereNotNull('hook')
                                  ->where('hook', '!=', '')
                                  ->get();

            foreach ($menusWithHooks as $menu) {
                // Registra o callback diretamente para o gancho exato salvo no banco (ex: "public.main_menu")
                HookManager::register($menu->hook, function($params) use ($menu) {

                    $rootItems = $menu->rootItems;
                    if ($rootItems->isEmpty()) {
                        return '';
                    }

                    // Renderiza a estrutura do menu dinâmico
                    if (view()->exists('menus::public.root')) {
                        return view('menus::public.root', [
                            'items' => $rootItems
                        ])->render();
                    }

                    return '';
                }, 'Menus Plugin');
            }
        }

        // Injeta o link no menu lateral administrativo
        \App\Support\AdminMenu::add([
            'label' => 'Menus',
            'icon'  => 'menu',
            'route' => 'admin.menus.index',
            'active' => 'admin.menus.*',
            'role' => 'admin',
        ], 'Temas');
    }
}
