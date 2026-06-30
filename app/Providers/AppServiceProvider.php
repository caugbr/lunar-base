<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
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

            // Injeta os dados da KingHost salvos na sua interface/banco
            Config::set('mail.mailers.smtp.host',       setting('mail.mail_host'));
            Config::set('mail.mailers.smtp.port',       setting('mail.mail_port'));
            Config::set('mail.mailers.smtp.encryption', setting('mail.mail_encryption'));
            Config::set('mail.mailers.smtp.username',   setting('mail.mail_username'));
            Config::set('mail.mailers.smtp.password',   setting('mail.mail_password'));
            Config::set('mail.from.address',            setting('mail.mail_from_address'));
            Config::set('mail.from.name',               setting('mail.mail_from_name'));

            // Comando crucial: limpa a memória do MailManager para assumir o banco em vez do .env
            app()->forgetInstance('mail.manager');

            // Adiciona menu e legal pages
            View::composer('public.*', SiteComposer::class);

            $activePlugins = \App\Models\Plugin::where('is_active', true)->get();
            foreach ($activePlugins as $plugin) {
                // Pegamos a classe do Service Provider salva no banco (ex: "Plugins\Comments\CommentsServiceProvider")
                $providerClass = $plugin->service_provider_class;

                if (class_exists($providerClass)) {
                    $this->app->register($providerClass); // Aqui o gerenciador registra o plugin!
                }
            }

            /**
             * Diretiva @onceAsset($id)
             * Funciona como o @once nativo do Laravel, mas baseada no nosso ContentHelper
             * permitindo que o controle persista mesmo em conteúdos renderizados manualmente.
             */
            Blade::if('onceAsset', function ($id) {
                return ContentHelper::once($id);
            });
        }

        if ($this->app->runningInConsole()) {
            // Varre buscando tanto "plugins" quanto "Plugins", e tanto "database/migrations" quanto apenas "migrations"
            $pluginMigrations = array_merge(
                glob(base_path('plugins/*/database/migrations')),
                glob(base_path('Plugins/*/database/migrations')),
                glob(base_path('plugins/*/migrations')),
                glob(base_path('Plugins/*/migrations'))
            );

            // Remove caminhos duplicados ou falsos do array
            $pluginMigrations = array_filter(array_unique($pluginMigrations));

            if (!empty($pluginMigrations)) {
                $this->loadMigrationsFrom($pluginMigrations);
            }

            return;
        }
    }
}
