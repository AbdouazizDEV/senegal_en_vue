<?php

namespace App\Application\Traveler\TravelBook\Commands;

class AddPhotosCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $entryId,
        public readonly array $photoUrls
    ) {}
}


