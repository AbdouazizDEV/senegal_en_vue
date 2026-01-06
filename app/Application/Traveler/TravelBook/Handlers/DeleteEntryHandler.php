<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Commands\DeleteEntryCommand;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class DeleteEntryHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(DeleteEntryCommand $command): bool
    {
        $entry = $this->travelBookRepository->findById($command->entryId);

        if (!$entry || $entry->traveler_id !== $command->travelerId) {
            throw new \Exception('Entrée non trouvée ou accès non autorisé.');
        }

        return $this->travelBookRepository->delete($entry);
    }
}

