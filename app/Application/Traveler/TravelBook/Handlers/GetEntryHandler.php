<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Queries\GetEntryQuery;
use App\Domain\TravelBook\Models\TravelBookEntry;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;

class GetEntryHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(GetEntryQuery $query): ?TravelBookEntry
    {
        $entry = is_numeric($query->entryId)
            ? $this->travelBookRepository->findById($query->entryId)
            : $this->travelBookRepository->findByUuid($query->entryId);

        if (!$entry || $entry->traveler_id !== $query->travelerId) {
            return null;
        }

        // Incrémenter le compteur de vues
        $entry->increment('views_count');

        return $entry->fresh(['experience', 'booking', 'traveler']);
    }
}


