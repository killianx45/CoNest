<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('access-admin', function ($user) {
            return $user->role === 'ROLE_ADMIN';
        });

        Gate::define('manage-products', function ($user) {
            return in_array($user->role, ['ROLE_LOUEUR', 'ROLE_ADMIN']);
        });

        Gate::define('view-products', function ($user) {
            return in_array($user->role, ['ROLE_USER', 'ROLE_LOUEUR', 'ROLE_ADMIN']);
        });

        Gate::define('create-orders', function ($user) {
            return in_array($user->role, ['ROLE_USER', 'ROLE_LOUEUR', 'ROLE_ADMIN']);
        });
    }
}
