<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Grupo API (padrão do Laravel Sanctum)
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Aliases para middlewares personalizados
        $middleware->alias([
            'partner.auth' => \App\Http\Middleware\AuthenticatePartner::class,
            'validate.domain' => \App\Http\Middleware\ValidateDomain::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // ✅ EXCLUIR ROTAS API DO CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/v1/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
