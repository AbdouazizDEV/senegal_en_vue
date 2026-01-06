<?php

namespace App\Application\Favorite\Queries;

readonly class GetFavoritesAlertsQuery
{
    public function __construct(
        public int $userId
    ) {}
}


