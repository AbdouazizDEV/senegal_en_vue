<?php

namespace App\Application\Favorite\Handlers;

use App\Application\Favorite\Queries\GetFavoritesAlertsQuery;
use App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface;
use App\Presentation\Http\Resources\ExperienceResource;

class GetFavoritesAlertsHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(GetFavoritesAlertsQuery $query): \Illuminate\Support\Collection
    {
        $favorites = $this->favoriteRepository->getFavoritesWithAlerts($query->userId);

        return $favorites->map(function ($favorite) {
            return [
                'favorite_id' => $favorite->id,
                'experience' => new ExperienceResource($favorite->experience),
                'alerts' => [
                    'price_drop' => $favorite->notify_on_price_drop,
                    'availability' => $favorite->notify_on_availability,
                    'new_reviews' => $favorite->notify_on_new_reviews,
                ],
                'notified_at' => $favorite->notified_at?->toIso8601String(),
            ];
        });
    }
}

