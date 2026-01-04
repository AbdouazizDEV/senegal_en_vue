<?php

namespace App\Application\Favorite\Queries;

readonly class GetFavoritesQuery
{
    public function __construct(
        public int $userId,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}

