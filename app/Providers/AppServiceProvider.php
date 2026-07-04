<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use App\View\Composers\SiteComposer;
use Illuminate\Support\Facades\Blade;
use App\Helpers\ContentHelper;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // O "if" garante que o Laravel não quebre quando você rodar comandos no terminal (como php artisan migrate)
        if (!app()->runningInConsole() || app()->runningUnitTests()) {

            // Adiciona menu e legal pages
            View::composer('public.*', SiteComposer::class);

            /**
             * Diretiva @onceAsset($id)
             * Funciona como o @once nativo do Laravel, mas baseada no nosso ContentHelper
             * permitindo que o controle persista mesmo em conteúdos renderizados manualmente.
             */
            Blade::if('onceAsset', function ($id) {
                return ContentHelper::once($id);
            });
        }
    }
}
