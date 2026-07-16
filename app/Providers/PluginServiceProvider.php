<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Plugin;

class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // SEMPRE registra as views de ajuda de TODOS os plugins
        $this->registerPluginHelpViews();

        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->registerPluginMigrations();
            return;
        }

        // Rotas dos plugins ATIVOS (ANTES das catch-all)
        $this->registerActivePluginRoutes();

        // Service providers, hooks, shortcodes (só plugins ativos)
        $this->registerActivePlugins();

        // Listar os hooks
        // \App\Support\AdminMenu::add([
        //     'label'  => 'Hooks',
        //     'icon'   => 'plug',
        //     'route'  => 'admin.hooks.index',
        //     'active' => 'admin.hooks.*',
        //     'role'   => 'admin',
        // ], 'Configuracoes');
    }

    /**
     * Registra as views de ajuda de TODOS os plugins.
     * Namespace: {kebab-name}-help::index
     */
    private function registerPluginHelpViews(): void
    {
        $pluginsPath = base_path('plugins');

        if (!File::isDirectory($pluginsPath)) {
            return;
        }

        foreach (File::directories($pluginsPath) as $pluginPath) {
            $folderName = basename($pluginPath);
            $kebabName = Str::kebab($folderName);
            $helpPath = $pluginPath . '/resources/help-views';

            if (File::isDirectory($helpPath)) {
                $this->loadViewsFrom($helpPath, "{$kebabName}-help");
            }
        }
    }

    /**
     * Registra as rotas de cada plugin ATIVO.
     */
    private function registerActivePluginRoutes(): void
    {
        $activePlugins = Plugin::where('is_active', true)->get();

        foreach ($activePlugins as $plugin) {
            $folder = $plugin->folder_name;
            $basePath = base_path("plugins/{$folder}");

            // Rotas admin
            $adminRoutes = "{$basePath}/routes.php";
            if (File::exists($adminRoutes)) {
                Route::middleware(['web', 'auth'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group($adminRoutes);
            }

            // Rotas públicas
            $publicRoutes = "{$basePath}/routes-public.php";
            if (File::exists($publicRoutes)) {
                Route::middleware(['web'])
                    ->name("plugin.{$folder}.")
                    ->group($publicRoutes);
            }
        }
    }

    private function registerActivePlugins(): void
    {
        Plugin::where('is_active', true)
            ->pluck('service_provider_class')
            ->filter(fn($class) => class_exists($class))
            ->each(fn($class) => $this->app->register($class));
    }

    // private function registerPluginMigrations(): void
    // {
    //     $paths = array_filter(glob(base_path('plugins/*/database/migrations')));

    //     if (!empty($paths)) {
    //         $this->loadMigrationsFrom($paths);
    //     }
    // }

    private function registerPluginMigrations(): void
    {
        $activePlugins = Plugin::where('is_active', true)->get();
        $paths = [];

        foreach ($activePlugins as $plugin) {
            $migrationsPath = base_path("plugins/{$plugin->folder_name}/database/migrations");

            if (File::isDirectory($migrationsPath)) {
                $paths[] = $migrationsPath;
            }
        }

        if (!empty($paths)) {
            $this->loadMigrationsFrom($paths);
        }
    }
}
