<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Allow if user has is_admin truthy, role == 'admin', or email in config list.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Prefer model helper if available (case-insensitive via User::hasRole)
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        // Check common admin markers without assuming DB schema (case-insensitive)
        if (!empty($user->is_admin) || (isset($user->role) && strtolower((string)$user->role) === 'admin')) {
            return $next($request);
        }

        $admins = config('orsm.admins', []);
        if (is_array($admins) && in_array($user->email, $admins)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Admins only.');
    }
}
