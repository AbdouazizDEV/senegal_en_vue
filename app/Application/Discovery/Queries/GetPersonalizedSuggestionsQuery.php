<?php

namespace App\Application\Discovery\Queries;

readonly class GetPersonalizedSuggestionsQuery
{
    public function __construct(
        public int $userId,
        public int $limit = 10
    ) {}
}



