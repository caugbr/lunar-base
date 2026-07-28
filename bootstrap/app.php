<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // ✅ EXCLUIR ROTAS API DO CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/v1/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ?
    })->create();

// === CARREGAMENTO PRECOCE DO .ENV ===
// Como o autoloader já rodou, podemos usar o Dotenv nativo do Laravel
if (file_exists(__DIR__.'/../.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(__DIR__.'/..')->safeLoad();
}

$publicPath = env('APP_PUBLIC_PATH') ?: $app->publicPath();
$app->usePublicPath($publicPath);

return $app;
