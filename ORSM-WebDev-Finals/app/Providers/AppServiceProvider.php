<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register role middleware alias so routes can use `role:admin` without editing Kernel.
        $this->app->make(Router::class)->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);

        // Alias to restrict admin checkout in production
        $this->app->make(Router::class)->aliasMiddleware('restrict_admin_checkout', \App\Http\Middleware\RestrictAdminCheckout::class);
    }
}
