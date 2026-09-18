<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permission)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
