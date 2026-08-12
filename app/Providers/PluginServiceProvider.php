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
        if (!dbAvailable('plugins')) return;

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
        if (!dbAvailable('plugins')) return;

        $activePlugins = Plugin::where('is_active', true)->get();

        foreach ($activePlugins as $plugin) {
            $pluginPath = base_path("plugins/{$plugin->folder_name}");

            // Se a pasta do plugin foi apagada do disco, desativa no banco e pula
            if (!File::isDirectory($pluginPath)) {
                $plugin->update(['is_active' => false]);
                continue;
            }

            // Carrega apenas os arquivos de Funções Globais (evitando Classes PSR-4)
            $this->registerPluginHelpers($pluginPath);

            // Como a pasta existe no disco, é seguro chamar o class_exists
            if (class_exists($plugin->service_provider_class)) {
                $this->app->register($plugin->service_provider_class);
            }
        }
    }

    private function registerPluginMigrations(): void
    {
        if (!dbAvailable('plugins')) return;

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

    /**
     * Carrega cuidadosamente os arquivos de funções/helpers do plugin.
     * Normaliza caminhos de arquivo (Linux/Windows) e ignora Classes PSR-4.
     */
    private function registerPluginHelpers(string $pluginPath): void
    {
        $filesToInclude = [];

        // 1. Suporte a arquivo helper.php solto na raiz do plugin
        $rootHelper = "{$pluginPath}/helper.php";
        if (File::exists($rootHelper)) {
            $filesToInclude[] = $rootHelper;
        }

        // 2. Suporte a todos os arquivos .php dentro da pasta /Helpers
        $helpersDir = "{$pluginPath}/Helpers";
        if (File::isDirectory($helpersDir)) {
            $filesToInclude = array_merge($filesToInclude, File::glob("{$helpersDir}/*.php"));
        }

        foreach ($filesToInclude as $file) {
            // Normaliza barras para funcionar perfeitamente tanto em Linux quanto Windows
            $cleanFile     = str_replace('\\', '/', $file);
            $cleanBasePath = str_replace('\\', '/', base_path('plugins/'));

            // Extrai o caminho relativo (ex: "Menus/Helpers/MenuHelper.php" ou "Forms/helper.php")
            $relativePath  = Str::after($cleanFile, $cleanBasePath);

            // Monta o FQCN esperado da classe (ex: "Plugins\Menus\Helpers\MenuHelper")
            $classFqcn     = 'Plugins\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            // Se o Composer (PSR-4) conseguir carregar como uma Classe válida,
            // ignora o require manual e deixa o Composer gerenciar por demanda
            if (class_exists($classFqcn, true)) {
                continue;
            }

            // Se for um arquivo de funções globais (ex: renderForm(), renderMenu()), carrega com segurança
            require_once $file;
        }
    }
}
