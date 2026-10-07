<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\PublicationTypes;

class InitHook
{
    public function handle(Request $request, Closure $next): Response
    {
        // Amarramos os hooks nos Models registrados (do Core e dos Plugins)
        PublicationTypes::bootHooks();

        // Avisamos os plugins que o sistema está 100% pronto e o Auth disponível
        doAction('init');

        return $next($request);
    }
}
