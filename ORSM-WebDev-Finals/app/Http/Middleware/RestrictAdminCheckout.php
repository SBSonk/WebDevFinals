<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictAdminCheckout
{
    /**
     * Always block admins from accessing checkout or creating orders,
     * regardless of environment.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $isAdmin = false;
            if (method_exists($user, 'isAdmin')) {
                $isAdmin = $user->isAdmin();
            } elseif (isset($user->role)) {
                $isAdmin = strtolower((string) $user->role) === 'admin';
            }

            if ($isAdmin) {
                // Friendly 403 message specific to policy
                abort(403, 'Checkout is disabled for admin accounts.');
            }
        }

        return $next($request);
    }
}
