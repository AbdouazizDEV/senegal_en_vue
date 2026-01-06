<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Commands\ShareTravelBookCommand;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class ShareTravelBookHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(ShareTravelBookCommand $command): array
    {
        $filters = [];

        if ($command->entryIds) {
            // Partager seulement les entrées spécifiées
            $entries = $this->travelBookRepository->findByTravelerId(
                $command->travelerId,
                $filters,
                1000
            );

            $updatedCount = 0;
            foreach ($entries as $entry) {
                if (in_array($entry->id, $command->entryIds)) {
                    $this->travelBookRepository->update($entry, ['visibility' => $command->visibility]);
                    $updatedCount++;
                }
            }
        } else {
            // Partager tout le carnet
            $entries = $this->travelBookRepository->findByTravelerId(
                $command->travelerId,
                $filters,
                1000
            );

            $updatedCount = 0;
            foreach ($entries as $entry) {
                $this->travelBookRepository->update($entry, ['visibility' => $command->visibility]);
                $updatedCount++;
            }
        }

        return [
            'message' => 'Carnet partagé avec succès',
            'visibility' => $command->visibility,
            'entries_updated' => $updatedCount,
        ];
    }
}

