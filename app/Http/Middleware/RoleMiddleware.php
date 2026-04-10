<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Se você usa role_id (FK)
        // $userRole = $user->role?->slug;

        // Se você usa role (string)
        $userRole = $user->role;

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
