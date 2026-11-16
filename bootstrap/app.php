<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;


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

        $middleware->appendToGroup('web', \App\Http\Middleware\CheckAddonDependencies::class);

        // EXCLUIR ROTAS API DO CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/v1/*',
        ]);

        // Apenas executa o hook 'init'
        $middleware->web(append: [
            \App\Http\Middleware\InitHook::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Quando o token CSRF expirar (Erro 419):
        // $exceptions->render(function (TokenMismatchException $e) {
        //     return redirect()->route('login')
        //         ->with('warning', 'Sua sessão expirou por inatividade. Faça login novamente.');
        // });

        // Intercepta qualquer 404 (página não encontrada)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {

            // Verifica se a URL começa com 'admin' ou 'admin/*'
            if ($request->is('admin', 'admin/*')) {

                // Se o usuário estiver autenticado:
                if (auth()->check()) {
                    return redirect()
                        ->to('/admin/dashboard') // ou route('admin.dashboard')
                        ->with('warning', "A página buscada ('/{$request->path()}') não existe!");
                }

                // Se NÃO estiver logado: deixa seguir o fluxo normal do 404
                return null;
            }

            return null;
        });
    })->create();

// === CARREGAMENTO PRECOCE DO .ENV ===
// Como o autoloader já rodou, podemos usar o Dotenv nativo do Laravel
if (file_exists(__DIR__.'/../.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(__DIR__.'/..')->safeLoad();
}

$publicPath = env('APP_PUBLIC_PATH') ?: $app->publicPath();
$app->usePublicPath($publicPath);

return $app;
