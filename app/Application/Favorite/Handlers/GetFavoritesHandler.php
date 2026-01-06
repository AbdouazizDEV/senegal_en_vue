<?php

namespace App\Application\Favorite\Handlers;

use App\Application\Favorite\Queries\GetFavoritesQuery;
use App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFavoritesHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(GetFavoritesQuery $query): LengthAwarePaginator
    {
        return $this->favoriteRepository->findByUser(
            $query->userId,
            $query->page,
            $query->perPage
        );
    }
}



