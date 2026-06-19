<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

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
            Config::set('mail.mailers.smtp.host',       setting('mail_host'));
            Config::set('mail.mailers.smtp.port',       setting('mail_port'));
            Config::set('mail.mailers.smtp.encryption', setting('mail_encryption'));
            Config::set('mail.mailers.smtp.username',   setting('mail_username'));
            Config::set('mail.mailers.smtp.password',   setting('mail_password'));

            Config::set('mail.from.address',            setting('mail_from_address'));
            Config::set('mail.from.name',               setting('mail_from_name'));

            // Comando crucial: limpa a memória do MailManager para assumir o banco em vez do .env
            app()->forgetInstance('mail.manager');
        }
    }
}
