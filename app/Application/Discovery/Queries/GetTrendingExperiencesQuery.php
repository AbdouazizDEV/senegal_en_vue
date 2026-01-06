<?php

namespace App\Application\Discovery\Queries;

readonly class GetTrendingExperiencesQuery
{
    public function __construct(
        public int $limit = 10
    ) {}
}


