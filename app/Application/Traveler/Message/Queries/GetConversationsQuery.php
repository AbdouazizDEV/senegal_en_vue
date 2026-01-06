<?php

namespace App\Application\Traveler\Message\Queries;

class GetConversationsQuery
{
    public function __construct(
        public readonly int $travelerId,
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}


