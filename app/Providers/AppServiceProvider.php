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
        // Enregistrer les repositories
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
