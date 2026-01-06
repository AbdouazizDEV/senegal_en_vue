<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Commands\UpdateEntryCommand;
use App\Domain\TravelBook\Models\TravelBookEntry;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class UpdateEntryHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(UpdateEntryCommand $command): TravelBookEntry
    {
        $entry = $this->travelBookRepository->findById($command->entryId);

        if (!$entry || $entry->traveler_id !== $command->travelerId) {
            throw new \Exception('Entrée non trouvée ou accès non autorisé.');
        }

        $data = [];

        if ($command->title !== null) {
            $data['title'] = $command->title;
        }

        if ($command->content !== null) {
            $data['content'] = $command->content;
        }

        if ($command->entryDate !== null) {
            $data['entry_date'] = $command->entryDate;
        }

        if ($command->location !== null) {
            $data['location'] = $command->location;
        }

        if ($command->locationDetails !== null) {
            $data['location_details'] = $command->locationDetails;
        }

        if ($command->tags !== null) {
            $data['tags'] = $command->tags;
        }

        if ($command->visibility !== null) {
            $data['visibility'] = $command->visibility;
        }

        if ($command->metadata !== null) {
            $data['metadata'] = $command->metadata;
        }

        return $this->travelBookRepository->update($entry, $data);
    }
}

