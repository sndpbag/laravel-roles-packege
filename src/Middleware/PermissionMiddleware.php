<?php

namespace Sndpbag\DynamicRoles\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        if (!method_exists($user, 'hasPermission')) {
            abort(500, 'User model must use HasRolesAndPermissions trait');
        }

        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have the required permission to access this resource.');
        }

        return $next($request);
    }
}