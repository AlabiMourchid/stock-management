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
        Gate::define('admin', fn($user) => $user->role === 'admin');

        Gate::define('access-pos', fn($user) => in_array($user->role, ['admin', 'caissier']));

        Gate::define('view-reports', fn($user) => in_array($user->role, ['admin']));

        Gate::define('manage-stock', fn($user) => in_array($user->role, ['admin']));

        Gate::define('cuisine-actions', fn($user) => in_array($user->role, ['admin', 'cuisinier']));

    }
}
