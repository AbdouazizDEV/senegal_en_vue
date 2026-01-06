<?php

namespace App\Application\Traveler\TravelBook\Commands;

class DeleteEntryCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $entryId
    ) {}
}

