<?php

namespace Sndpbag\DynamicRoles\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        if (!method_exists($user, 'hasRole')) {
            abort(500, 'User model must use HasRolesAndPermissions trait');
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}