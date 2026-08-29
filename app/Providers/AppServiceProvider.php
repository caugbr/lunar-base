<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use App\View\Composers\SiteComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use App\Helpers\ContentHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Auth\Notifications\VerifyEmail; // Adicionado
use Illuminate\Notifications\Messages\MailMessage; // Adicionado
use App\Services\AssetManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registra como Singleton para manter a fila durante todo o ciclo de vida da requisição
        $this->app->singleton(AssetManager::class, function () {
            return new AssetManager();
        });
    }

    public function boot(): void
    {
        // O "if" garante que o Laravel não quebre quando você rodar comandos no terminal (como php artisan migrate)
        if (!app()->runningInConsole() || app()->runningUnitTests()) {

            Paginator::defaultView('vendor.pagination.custom');

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

            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });

            // Personaliza o e-mail nativo de verificação para usar a sua view Blade (emails.verify-email)
            VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
                return (new MailMessage)
                    ->subject('Confirme seu e-mail - ' . setting('general.site_name', config('app.name')))
                    ->view('emails.verify-email', [
                        'url'  => $url,
                        'user' => $notifiable,
                    ]);
            });

            // Configura o SMTP dinamicamente usando os seus dados de settings
            try {
                if (function_exists('setting')) {
                    $mailHost = setting('mail.mail_host');

                    if (!empty($mailHost)) {
                        Config::set('mail.default', 'smtp');
                        Config::set('mail.mailers.smtp.host', $mailHost);
                        Config::set('mail.mailers.smtp.port', setting('mail.mail_port', 587));
                        Config::set('mail.mailers.smtp.encryption', setting('mail.mail_encryption', 'tls'));
                        Config::set('mail.mailers.smtp.username', setting('mail.mail_username'));
                        Config::set('mail.mailers.smtp.password', setting('mail.mail_password'));
                        Config::set('mail.from.address', setting('mail.mail_from_address'));
                        Config::set('mail.from.name', setting('mail.mail_from_name'));
                    }
                }
            } catch (\Throwable $e) {
                // Evita quebrar comandos Artisan caso o banco ainda não tenha sido migrado
            }

        }

        // Diretivas Blade estilo WordPress
        Blade::directive('headerAssets', function () {
            return "<?php
            echo app(\\App\Services\\AssetManager::class)->renderStyles();
            echo app(\\App\Services\\AssetManager::class)->renderScripts(false);
            ?>";
        });

        Blade::directive('footerAssets', function () {
            return "<?php echo app(\\App\Services\\AssetManager::class)->renderScripts(true); ?>";
        });
    }
}
