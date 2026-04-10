<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// class CheckRole
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
//      */
//     public function handle(Request $request, Closure $next, $role): Response
//     {
//         if (!auth()->check() || auth()->user()->role !== $role) {
//             abort(403, 'Acesso não autorizado');
//         }

//         return $next($request);
//     }
// }
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRoleSlug = auth()->user()->role?->slug;

        if (in_array($userRoleSlug, $roles)) {
            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
