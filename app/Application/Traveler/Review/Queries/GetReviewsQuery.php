<?php

namespace App\Application\Traveler\Review\Queries;

class GetReviewsQuery
{
    public function __construct(
        public readonly int $travelerId,
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}

