<?php

namespace App\Application\Traveler\TravelBook\Commands;

class UpdateEntryCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $entryId,
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?string $entryDate = null,
        public readonly ?string $location = null,
        public readonly ?array $locationDetails = null,
        public readonly ?array $tags = null,
        public readonly ?string $visibility = null,
        public readonly ?array $metadata = null
    ) {}
}


