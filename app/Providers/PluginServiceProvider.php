<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Plugin;

class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->registerPluginMigrations();
            return;
        }

        $this->registerActivePlugins();
    }

    private function registerActivePlugins(): void
    {
        Plugin::where('is_active', true)
            ->pluck('service_provider_class')
            ->filter(fn($class) => class_exists($class))
            ->each(fn($class) => $this->app->register($class));
    }

    private function registerPluginMigrations(): void
    {
        $paths = array_filter(glob(base_path('plugins/*/database/migrations')));

        if (!empty($paths)) {
            $this->loadMigrationsFrom($paths);
        }
    }
}
