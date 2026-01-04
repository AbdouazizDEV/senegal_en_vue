<?php

namespace App\Application\Favorite\Handlers;

use App\Application\Favorite\Commands\RemoveFavoriteCommand;
use App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface;

class RemoveFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(RemoveFavoriteCommand $command): bool
    {
        $deleted = $this->favoriteRepository->delete(
            $command->userId,
            $command->experienceId
        );

        if (!$deleted) {
            throw new \Exception('Cette expérience n\'est pas dans vos favoris.');
        }

        return true;
    }
}

