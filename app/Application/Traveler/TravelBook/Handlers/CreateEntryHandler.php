<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Commands\CreateEntryCommand;
use App\Domain\TravelBook\Models\TravelBookEntry;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class CreateEntryHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(CreateEntryCommand $command): TravelBookEntry
    {
        $data = [
            'traveler_id' => $command->travelerId,
            'title' => $command->title,
            'content' => $command->content,
            'entry_date' => $command->entryDate,
            'visibility' => $command->visibility,
        ];

        if ($command->experienceId) {
            $data['experience_id'] = $command->experienceId;
        }

        if ($command->bookingId) {
            $data['booking_id'] = $command->bookingId;
        }

        if ($command->location) {
            $data['location'] = $command->location;
        }

        if ($command->locationDetails) {
            $data['location_details'] = $command->locationDetails;
        }

        if ($command->tags) {
            $data['tags'] = $command->tags;
        }

        if ($command->metadata) {
            $data['metadata'] = $command->metadata;
        }

        return $this->travelBookRepository->create($data);
    }
}


