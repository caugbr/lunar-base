<?php

namespace Plugins\Populator;

use Illuminate\Support\ServiceProvider;

class PopulatorServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'populator');

        \App\Support\AdminMenu::add([
            'label' => 'Populator',
            'icon'  => 'flask-conical',
            'route' => 'admin.populator.index',
            'active' => 'admin.populator.*',
            'role' => 'admin',
        ], 'Configurações');
    }
}
