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
