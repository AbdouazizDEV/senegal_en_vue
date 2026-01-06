<?php

namespace App\Application\Traveler\TravelBook\Queries;

class GetEntryQuery
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int|string $entryId // Peut être ID ou UUID
    ) {}
}

