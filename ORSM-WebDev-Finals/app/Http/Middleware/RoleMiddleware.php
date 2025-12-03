<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Expected parameter format: role:admin or role:admin,staff
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if ($roles === null) {
            return $next($request);
        }

        $rolesArray = array_map('trim', explode(',', $roles));

        if (! $user->hasAnyRole($rolesArray)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
