<?php

namespace App\Application\Favorite\Handlers;

use App\Application\Favorite\Commands\AddFavoriteCommand;
use App\Domain\Experience\Models\Experience;
use App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface;

class AddFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(AddFavoriteCommand $command): \App\Domain\Favorite\Models\Favorite
    {
        // Vérifier que l'expérience existe
        $experience = Experience::findOrFail($command->experienceId);

        // Vérifier si déjà en favoris
        $existing = $this->favoriteRepository->findByUserAndExperience(
            $command->userId,
            $command->experienceId
        );

        if ($existing) {
            throw new \Exception('Cette expérience est déjà dans vos favoris.');
        }

        return $this->favoriteRepository->create(
            $command->userId,
            $command->experienceId,
            [
                'notify_on_price_drop' => $command->notifyOnPriceDrop,
                'notify_on_availability' => $command->notifyOnAvailability,
                'notify_on_new_reviews' => $command->notifyOnNewReviews,
            ]
        );
    }
}


