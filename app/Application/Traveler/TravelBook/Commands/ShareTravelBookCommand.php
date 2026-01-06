<?php

namespace App\Application\Traveler\TravelBook\Commands;

class ShareTravelBookCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly string $visibility, // 'friends' ou 'public'
        public readonly ?array $entryIds = null // Si null, partage tout le carnet
    ) {}
}

