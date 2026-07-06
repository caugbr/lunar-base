<?php

namespace Plugins\Maintenance\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Se o modo de manutenção estiver desativado, permite fluxo normal
        if (!setting('maintenance.maintenance_enabled', false)) {
            return $next($request);
        }

        // 2. WHITELIST: Permite rotas administrativas, de login/autenticação e o teste de integridade "up"
        if ($request->is('admin*') ||
            $request->is('login*') ||
            $request->is('logout*') ||
            $request->is('two-factor*') ||
            $request->is('up')) {
            return $next($request);
        }

        // 3. WHITELIST: Permite navegação se o usuário logado for admin ou editor (usando helper isRole do core)
        if (auth()->check() && (isRole('admin') || isRole('editor'))) {
            return $next($request);
        }

        // 4. Bloqueia acesso comum e renderiza a view com cabeçalho de status HTTP 503 (Serviço Indisponível)
        return response()->view('maintenance::public.maintenance', [], 503);
    }
}
