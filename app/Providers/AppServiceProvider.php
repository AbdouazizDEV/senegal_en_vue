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
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentExperienceRepository::class
        );
        
        // Enregistrer les handlers (auto-wiring via constructeur, mais on peut aussi les enregistrer explicitement)
        // Les handlers sont automatiquement résolus par Laravel grâce à l'injection de dépendances
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
