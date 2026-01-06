<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Commands\AddPhotosCommand;
use App\Domain\TravelBook\Models\TravelBookEntry;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class AddPhotosHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(AddPhotosCommand $command): TravelBookEntry
    {
        $entry = $this->travelBookRepository->findById($command->entryId);

        if (!$entry || $entry->traveler_id !== $command->travelerId) {
            throw new \Exception('Entrée non trouvée ou accès non autorisé.');
        }

        return $this->travelBookRepository->addPhotos($entry, $command->photoUrls);
    }
}

