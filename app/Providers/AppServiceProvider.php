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
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentBookingRepository::class
        );
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentPaymentRepository::class
        );
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentReviewRepository::class
        );
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentContentRepository::class
        );
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentCertificationRepository::class
        );
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentFavoriteRepository::class
        );
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentDiscoveryRepository::class
        );
        
        $this->app->bind(
            \App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface::class,
            \App\Infrastructure\Repositories\Eloquent\EloquentTravelBookRepository::class
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
