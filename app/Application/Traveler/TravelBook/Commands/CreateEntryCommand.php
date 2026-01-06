<?php

namespace App\Application\Traveler\TravelBook\Commands;

class CreateEntryCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly ?int $experienceId = null,
        public readonly ?int $bookingId = null,
        public readonly string $title,
        public readonly string $content,
        public readonly string $entryDate,
        public readonly ?string $location = null,
        public readonly ?array $locationDetails = null,
        public readonly ?array $tags = null,
        public readonly string $visibility = 'private',
        public readonly ?array $metadata = null
    ) {}
}


