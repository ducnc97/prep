<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // middleware
        $this->app->singleton(\App\Http\Middleware\LogRequest::class);

        // services
        $this->app->singleton(\App\Services\AuthServiceInterface::class, \App\Services\AuthService::class);
        $this->app->singleton(\App\Services\CheckLocationFraudServiceInterface::class, \App\Services\CheckLocationFraudService::class);

        // repositoris
        $this->app->singleton(\App\Services\RepositoryInterface\AuthRepositoryInterface::class, \App\Repositories\AuthRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
