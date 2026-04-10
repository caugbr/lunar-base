<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleId
{
    public function handle(Request $request, Closure $next, ...$roleIds)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRoleId = auth()->user()->role_id;

        if (in_array($userRoleId, $roleIds)) {
            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
