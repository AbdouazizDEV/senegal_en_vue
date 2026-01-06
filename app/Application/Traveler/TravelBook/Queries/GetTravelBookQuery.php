<?php

namespace App\Application\Traveler\TravelBook\Queries;

class GetTravelBookQuery
{
    public function __construct(
        public readonly int $travelerId,
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}

