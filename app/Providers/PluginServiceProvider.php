<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Plugin;

class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // SEMPRE registra as views de ajuda de TODOS os plugins
        $this->registerPluginHelpViews();

        if (!dbAvailable('plugins')) {
            return;
        }

        // Busca os plugins ativos UMA ÚNICA VEZ por requisição/execução
        $activePlugins = Plugin::where('is_active', true)->get();

        if ($activePlugins->isEmpty()) {
            return;
        }

        // Migrações no console
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->registerPluginMigrations($activePlugins);
        }

        // Valida e registra cada plugin ativo (rotas, helpers e service providers)
        $this->bootActivePlugins($activePlugins);
    }

    /**
     * Valida integridade física, rotas, helpers e registra os Providers.
     */
    private function bootActivePlugins($activePlugins): void
    {
        $isConsole = app()->runningInConsole();

        foreach ($activePlugins as $plugin) {
            $pluginPath = base_path("plugins/{$plugin->folder_name}");

            // Caso o plugin ativo tenha sido apagado do disco
            if (!File::isDirectory($pluginPath)) {
                $plugin->update(['is_active' => false]);
                Log::warning("Plugin '{$plugin->name}' desativado automaticamente: diretório não encontrado ({$pluginPath}).");
                continue;
            }

            // Rotas só precisam ser registradas em requisições Web/API (evita overhead no console)
            if (!$isConsole) {
                $this->registerPluginRoutes($plugin, $pluginPath);
            }

            // Helpers globais
            $this->registerPluginHelpers($pluginPath);

            // Registra o Service Provider do plugin
            if ($plugin->service_provider_class) {
                if (class_exists($plugin->service_provider_class)) {
                    $this->app->register($plugin->service_provider_class);
                } else {
                    // Pasta existe, mas a classe principal não foi encontrada/autoload falhou
                    $plugin->update(['is_active' => false]);
                    Log::warning("Plugin '{$plugin->name}' desativado: classe '{$plugin->service_provider_class}' não existe.");
                }
            }
        }
    }

    /**
     * Registra as rotas de um plugin individual.
     */
    private function registerPluginRoutes(Plugin $plugin, string $pluginPath): void
    {
        $folder = $plugin->folder_name;

        // Rotas admin
        $adminRoutes = "{$pluginPath}/routes.php";
        if (File::exists($adminRoutes)) {
            Route::middleware(['web', 'auth'])
                ->prefix('admin')
                ->name('admin.')
                ->group($adminRoutes);
        }

        // Rotas públicas
        $publicRoutes = "{$pluginPath}/routes-public.php";
        if (File::exists($publicRoutes)) {
            Route::middleware(['web'])
                ->name("plugin.{$folder}.")
                ->group($publicRoutes);
        }
    }

    /**
     * Registra migrações dos plugins ativos.
     */
    private function registerPluginMigrations($activePlugins): void
    {
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

    /**
     * Registra as views de ajuda de TODOS os plugins.
     */
    private function registerPluginHelpViews(): void
    {
        $pluginsPath = base_path('plugins');

        if (!File::isDirectory($pluginsPath)) {
            return;
        }

        foreach (File::directories($pluginsPath) as $pluginPath) {
            $folderName = basename($pluginPath);
            $helpPath = $pluginPath . '/resources/help-views';

            if (File::isDirectory($helpPath)) {
                // Insere espaço antes de maiúsculas seguidas de minúsculas
                $spacedName = preg_replace('/(?<!^)(?=[A-Z][a-z])/', ' ', $folderName);
                $slugName = Str::slug($spacedName);

                $this->loadViewsFrom($helpPath, "{$slugName}-help");
            }
        }
    }

    /**
     * Carrega arquivos de helpers ignorando classes PSR-4.
     */
    private function registerPluginHelpers(string $pluginPath): void
    {
        $filesToInclude = [];

        $rootHelper = "{$pluginPath}/helper.php";
        if (File::exists($rootHelper)) {
            $filesToInclude[] = $rootHelper;
        }

        $helpersDir = "{$pluginPath}/Helpers";
        if (File::isDirectory($helpersDir)) {
            $filesToInclude = array_merge($filesToInclude, File::glob("{$helpersDir}/*.php") ?: []);
        }

        $cleanBasePath = str_replace('\\', '/', base_path('plugins/'));

        foreach ($filesToInclude as $file) {
            $cleanFile    = str_replace('\\', '/', $file);
            $relativePath = Str::after($cleanFile, $cleanBasePath);
            $classFqcn    = 'Plugins\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (class_exists($classFqcn, true)) {
                continue;
            }

            require_once $file;
        }
    }
}
