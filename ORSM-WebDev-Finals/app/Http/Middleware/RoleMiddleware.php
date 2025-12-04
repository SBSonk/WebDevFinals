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
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Laravel passes comma-separated params after the colon; if the middleware
        // uses variadics, they'll already be split. If not, we may get a single
        // item with commas — handle both.
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $rolesArray = array_map('trim', explode(',', $roles[0]));
        } else {
            $rolesArray = array_map('trim', $roles);
        }

        if (! $user->hasAnyRole($rolesArray)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
